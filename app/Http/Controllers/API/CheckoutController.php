<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PrintDesign;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Nhận đơn đặt hàng từ web bán hàng.
 *
 * Đây là endpoint công khai (khách không có tài khoản) nên mọi số tiền đều
 * do server tự tính từ CSDL, không nhận giá gửi lên từ trình duyệt.
 */
class CheckoutController extends Controller
{
    /**
     * Khớp mã hình thức thanh toán mà web bán hàng gửi lên với khoá trong cài đặt.
     * Mọi mã lạ đều coi là chuyển khoản, đúng như mặc định của trường payment_method.
     */
    private function settingKey(?string $paymentMethod): string
    {
        return $paymentMethod === 'cod' ? 'cod' : 'bank_transfer';
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20|regex:/^[0-9\s.+()-]+$/',
            'customer_email' => 'nullable|email|max:255',
            'province' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'note' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|max:50',
            // Chỉ StorefrontOrderController gọi nội bộ sau khi PayOS xác nhận. Nó phải
            // khớp với khóa QR tạm của từng mẫu in để không nhận tiền trùng hai lần.
            'storefront_ref' => 'nullable|string|max:32|regex:/^[A-Za-z0-9]+$/',

            /*
             * `items` được phép rỗng KHI đơn có mã thiết kế in: khách đặt in áo
             * thường không mua kèm hàng bán sẵn, và bắt họ thêm một món vô nghĩa
             * chỉ để đơn hợp lệ là bắt sai chỗ.
             */
            'items' => 'present|array',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',

            /*
             * Mã các mẫu áo khách đã thiết kế. Nhiều mẫu trong một đơn là chuyện
             * bình thường: mỗi mẫu là một món trong giỏ, nên đơn áo lớp có thể
             * gồm cùng một hình trên ba size, mỗi size một mẫu.
             *
             * Giá từng mẫu đã đóng băng lúc chốt thiết kế nên bên này chỉ đọc
             * lại, không tính lại.
             */
            'print_design_codes' => 'nullable|array|max:20',
            'print_design_codes.*' => 'string|max:24',

            /*
             * Tài khoản nhận hoàn tiền. Đơn in có thể bị từ chối sau khi đã thu
             * tiền, mà lúc đó hỏi lại khách qua điện thoại thì vừa chậm vừa dễ
             * nghe nhầm số tài khoản.
             */
            'refund_bank_name' => 'nullable|string|max:100',
            'refund_account_number' => 'nullable|string|max:40',
            'refund_account_name' => 'nullable|string|max:120',
        ]);

        $codes = array_values(array_unique($data['print_design_codes'] ?? []));
        $printDesigns = collect();

        if ($codes) {
            $printDesigns = PrintDesign::with('blank.product')->whereIn('code', $codes)->get();

            if ($printDesigns->count() !== count($codes)) {
                $missing = array_diff($codes, $printDesigns->pluck('code')->all());

                return response()->json([
                    'success' => false,
                    'error' => 'Không tìm thấy mẫu thiết kế ' . implode(', ', $missing) . '. Vui lòng thiết kế lại.',
                ], 422);
            }

            /*
             * Một mẫu chỉ đi được vào MỘT đơn. Không chặn thì khách mở lại tab cũ
             * bấm đặt lần nữa là bị tính tiền hai lần cho cùng một thiết kế.
             */
            $taken = $printDesigns->filter(fn (PrintDesign $d) => $d->invoice_id !== null);

            if ($taken->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Mẫu ' . $taken->pluck('code')->implode(', ') . ' đã được đặt rồi. Vui lòng thiết kế mẫu mới.',
                ], 409);
            }

            if (! empty($data['storefront_ref'])) {
                $reservedElsewhere = $printDesigns->filter(
                    fn (PrintDesign $d) => $d->pending_payment_ref !== $data['storefront_ref'],
                );

                if ($reservedElsewhere->isNotEmpty()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Mẫu ' . $reservedElsewhere->pluck('code')->implode(', ')
                            . ' không còn thuộc phiên thanh toán này.',
                    ], 409);
                }
            }
        }

        if (!$data['items'] && $printDesigns->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Đơn hàng đang trống.'], 422);
        }

        // Thả hàng của những đơn bỏ ngang TRƯỚC khi kiểm tồn, để đơn chưa thanh
        // toán không chặn được khách đang thật sự muốn mua.
        Invoice::cancelExpiredHolds();

        // Gộp các dòng trùng biến thể để không trừ kho hai lần cho cùng một món.
        $wanted = [];
        foreach ($data['items'] as $item) {
            $id = (int) $item['variant_id'];
            $wanted[$id] = ($wanted[$id] ?? 0) + (int) $item['quantity'];
        }

        // Phí giao hàng lấy từ cài đặt bán hàng, không phải hằng số trong mã nguồn.
        $paymentMethod = $data['payment_method'] ?? 'banking';
        $methodKey = $this->settingKey($paymentMethod);
        $salesSettings = Setting::sales();

        if (empty($salesSettings[$methodKey]['enabled'])) {
            return response()->json([
                'success' => false,
                'error' => 'Hình thức thanh toán này hiện không nhận đơn.',
            ], 422);
        }

        // Ngưỡng miễn phí giao hàng đếm theo số món trong đơn. Áo in cũng là món
        // phải giao, nên số lượng của nó được tính vào ngưỡng như mọi món khác.
        $itemCount = array_sum($wanted) + (int) $printDesigns->sum('qty');
        $shippingFee = Setting::shippingFeeFor($methodKey, $itemCount, $salesSettings);
        $shopShippingFee = Setting::shopShippingCost($methodKey, $itemCount, $salesSettings);

        DB::beginTransaction();
        try {
            // Khoá các dòng biến thể để hai khách đặt cùng lúc không bán quá tồn.
            $variants = ProductVariant::with('product')
                ->whereIn('id', array_keys($wanted))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0;

            // Giá in đã đóng băng lúc khách chốt thiết kế, kèm id phiên bản bảng
            // giá đã dùng. Đọc lại chứ KHÔNG tính lại: chủ shop có thể đã sửa
            // bảng giá trong lúc khách còn đang điền địa chỉ.
            $printFee = (int) $printDesigns->sum('total_price');

            foreach ($wanted as $variantId => $quantity) {
                $variant = $variants->get($variantId);

                if (!$variant || !$variant->product) {
                    throw new \RuntimeException('Sản phẩm không còn tồn tại.');
                }

                // Hàng không theo dõi tồn kho vẫn bán được dù kho ghi 0.
                if ($variant->product->manage_stock && $variant->quantity < $quantity) {
                    throw new \RuntimeException(
                        'Sản phẩm "' . $variant->product->product_name . '" (' . $variant->label
                        . ') chỉ còn ' . $variant->quantity . ' sản phẩm.'
                    );
                }

                // Giá lấy từ CSDL: ưu tiên giá riêng của biến thể, rồi giá khuyến mãi, rồi giá bán.
                $unitPrice = (int) ($variant->price_override
                    ?? $variant->product->discount_price
                    ?? $variant->product->sell_price);

                $lines[] = [
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ];
                $subtotal += $unitPrice * $quantity;
            }

            $customer = Customer::mergeByPhone([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'address' => $data['address'],
                'province' => $data['province'],
                'ward' => $data['ward'],
            ]);

            $invoice = Invoice::create([
                'invoice_type' => Invoice::TYPE_ORDER,
                'order_code' => $this->generateOrderCode(),
                'order_status' => Invoice::STATUS_PENDING,
                'customer_id' => $customer->id,
                'user_id' => $this->systemUserId(),
                'total_amount' => $subtotal + $printFee + $shippingFee,
                // Tách riêng tiền in khỏi tiền hàng: lúc tính lãi phải biết
                // khoản nào là phôi và khoản nào là công in. Đầu mối tới từng
                // mẫu đi chiều ngược lại, gắn ngay sau khi có id hoá đơn.
                'print_fee' => $printFee,
                'shipping_fee' => $shippingFee,
                // Khoản shop tự gánh: không cộng vào tiền khách trả, nhưng vẫn phải
                // lưu lại, nếu không thì lúc tính lãi khoản này biến mất.
                'shop_shipping_fee' => $shopShippingFee,
                'shipping_name' => $data['customer_name'],
                'shipping_phone' => $customer->customer_phone,
                'shipping_address' => implode(', ', [$data['address'], $data['ward'], $data['province']]),
                'payment_method' => $paymentMethod,
                // Kho bị trừ ngay lúc này, nên đơn phải có hạn: quá hạn mà không
                // nhận được tiền thì lệnh orders:cancel-expired trả hàng về kho.
                // COD không có hạn vì khách trả tiền khi nhận hàng.
                'payment_expires_at' => $paymentMethod === 'cod'
                    ? null
                    : now()->addMinutes((int) config('services.storefront.payment_window_minutes', 30)),
                // Chưa nhận được tiền: đơn chỉ được xác nhận sau khi chuyển khoản thành công.
                'pay_status' => 0,
                'note' => $data['note'] ?? null,
                'signature_name' => $data['customer_name'],
            ]);

            foreach ($lines as $line) {
                DB::table('product_invoices')->insert([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line['variant']->product_id,
                    'variant_id' => $line['variant']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // max(0) để hàng không theo dõi tồn kho không tụt xuống số âm.
                $line['variant']->quantity = max(0, $line['variant']->quantity - $line['quantity']);
                $line['variant']->save();
            }

            // Sản phẩm hết sạch tồn thì đánh dấu hết hàng.
            foreach (array_unique(array_map(fn($l) => $l['variant']->product_id, $lines)) as $productId) {
                $total = ProductVariant::where('product_id', $productId)->sum('quantity');
                // Hàng không theo dõi tồn kho không bao giờ bị gắn "hết hàng".
                Product::where('id', $productId)
                    ->where('manage_stock', true)
                    ->update(['status' => $total > 0 ? 1 : 2]);
            }

            /*
             * Phôi in có nối kho thì vẫn phải trừ tồn — nó là chiếc áo thật đi
             * ra khỏi kệ. Cố ý KHÔNG thêm nó vào $lines: tiền phôi đã nằm trong
             * giá thiết kế rồi, chèn thêm một dòng hàng nữa là tính tiền hai lần.
             * Ở đây tồn kho và tiền là hai việc tách rời.
             */
            foreach ($printDesigns as $printDesign) {
                // Chỉ đến đây mẫu mới thuộc một đơn thật. `store()` chỉ được gọi sau
                // khi PayOS đã xác nhận, nên chuyển draft → pending tại đây là lúc duy nhất
                // nó được phép vào hàng đợi duyệt. `invoice_id` cũng chặn mẫu được đặt hai lần.
                $printDesign->update([
                    'invoice_id' => $invoice->id,
                    'pending_payment_ref' => null,
                    'review_status' => PrintDesign::STATUS_PENDING,
                ]);

                if (!$printDesign->blank?->product_id) {
                    continue;
                }

                $blankVariant = ProductVariant::where('product_id', $printDesign->blank->product_id)
                    ->where('size', $printDesign->size)
                    ->where('color', $printDesign->color_name)
                    ->lockForUpdate()
                    ->first();

                if ($blankVariant) {
                    $blankVariant->quantity = max(0, $blankVariant->quantity - $printDesign->qty);
                    $blankVariant->save();
                    continue;
                }

                // Không tìm thấy biến thể khớp thì bỏ qua trong im lặng là sai,
                // nhưng chặn đơn cũng sai: khách đã thiết kế xong và đang trả
                // tiền. Ghi log để nhân viên chỉnh tồn tay.
                Log::warning(sprintf(
                    'Đơn in %s: không tìm thấy biến thể %s/%s của sản phẩm #%d để trừ tồn.',
                    $printDesign->code,
                    $printDesign->size,
                    $printDesign->color_name,
                    $printDesign->blank->product_id,
                ));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_code' => $invoice->order_code,
                'subtotal' => $subtotal,
                'print_fee' => $printFee,
                'shipping_fee' => $shippingFee,
                'total_amount' => $subtotal + $printFee + $shippingFee,
                'message' => 'Đã nhận đơn hàng. Đơn sẽ được xác nhận sau khi nhận được chuyển khoản.',
            ], 201);
        } catch (\RuntimeException $e) {
            DB::rollBack();
            // Lỗi nghiệp vụ (hết hàng, sản phẩm đã xoá): nói rõ cho khách biết.
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Nhận đơn từ web bán hàng thất bại: ' . $e->getMessage());
            // Không lộ chi tiết lỗi hệ thống ra ngoài.
            return response()->json([
                'success' => false,
                'error' => 'Không tạo được đơn hàng, vui lòng thử lại.',
            ], 500);
        }
    }

    /**
     * Web bán hàng báo đã nhận được tiền của đơn.
     *
     * Chỉ đánh dấu đã thanh toán, KHÔNG tự xác nhận đơn: việc xác nhận vẫn do
     * nhân viên bấm sau khi đối chiếu, để một lời gọi API không thể tự đẩy đơn
     * vào dây chuyền giao hàng.
     */
    public function markPaid(string $orderCode)
    {
        DB::beginTransaction();
        try {
            // Khoá dòng: lệnh orders:cancel-expired có thể đang xét đúng đơn này.
            $order = Invoice::orders()->where('order_code', $orderCode)->lockForUpdate()->first();

            if (!$order) {
                DB::rollBack();

                return response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
            }

            if ((int) $order->pay_status === 1) {
                DB::rollBack();

                // Cổng thanh toán có thể gọi lại nhiều lần cho cùng một đơn.
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn đã được ghi nhận thanh toán trước đó.',
                ]);
            }

            $order->pay_status = 1;

            if ($order->order_status === Invoice::STATUS_CANCELLED) {
                // Tiền về sau khi đơn đã bị huỷ vì quá hạn. KHÔNG tự xác nhận lại:
                // hàng đã được trả về kho và có thể đã bán cho người khác. Ghi nhận
                // đã trả tiền rồi để nhân viên xử lý tay (giao bù hoặc hoàn tiền).
                $order->note = trim(($order->note ? $order->note . "\n" : '')
                    . 'CẦN XỬ LÝ: nhận được tiền sau khi đơn đã huỷ quá hạn.');
                $order->save();

                DB::commit();

                Log::critical('Nhận được thanh toán cho đơn đã huỷ.', ['order_code' => $orderCode]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đơn đã bị huỷ trước đó; đã ghi nhận thanh toán để nhân viên xử lý tay.',
                ]);
            }

            // Đơn thường được xác nhận ngay sau khi đã trả tiền. Riêng đơn in phải giữ
            // "chờ xác nhận" cho tới khi nhân viên duyệt xong file; PrintDesignController sẽ
            // chuyển nó sang confirmed khi tất cả mẫu đều được duyệt.
            if ($order->order_status === Invoice::STATUS_PENDING && ! $order->printDesigns()->exists()) {
                $order->order_status = Invoice::STATUS_CONFIRMED;
            }

            // Đã trả tiền thì hạn thanh toán hết ý nghĩa; xoá để lệnh quét bỏ qua đơn này.
            $order->payment_expires_at = null;
            $order->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Đã ghi nhận thanh toán.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Ghi nhận thanh toán thất bại: ' . $e->getMessage(), ['order_code' => $orderCode]);

            return response()->json(['error' => 'Không ghi nhận được thanh toán.'], 500);
        }
    }

    /**
     * Kiểm tra tồn trước khi khách bấm thanh toán.
     */
    public function checkStock(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Thả hàng của những đơn bỏ ngang TRƯỚC khi kiểm tồn, để đơn chưa thanh
        // toán không chặn được khách đang thật sự muốn mua.
        Invoice::cancelExpiredHolds();

        $variants = ProductVariant::with('product')
            ->whereIn('id', array_column($data['items'], 'variant_id'))
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($data['items'] as $item) {
            $variant = $variants->get((int) $item['variant_id']);
            $available = $variant->quantity ?? 0;
            // Hàng không theo dõi tồn kho luôn đủ: số tồn của nó chỉ để tham khảo.
            $unlimited = (bool) $variant && ! $variant->product?->manage_stock;

            $result[] = [
                'variant_id' => (int) $item['variant_id'],
                'available' => $available,
                'manage_stock' => ! $unlimited,
                'enough' => $unlimited || $available >= (int) $item['quantity'],
                'product' => $variant->product->product_name ?? null,
                'label' => $variant->label ?? null,
            ];
        }

        return response()->json([
            'ok' => collect($result)->every(fn($r) => $r['enough']),
            'items' => $result,
        ]);
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'DH' . now()->format('ymd') . strtoupper(Str::random(4));
        } while (Invoice::withTrashed()->where('order_code', $code)->exists());

        return $code;
    }

    /**
     * Đơn từ web không do nhân viên nào lập, nhưng invoices.user_id là bắt buộc,
     * nên gán cho tài khoản quản trị đầu tiên.
     */
    private function systemUserId(): ?int
    {
        return User::where('role', 1)->value('id') ?? User::value('id');
    }
}
