<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PrintDesign;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use App\Services\VoucherRedemption;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Nhận đơn đặt hàng từ web bán hàng.
 *
 * Đây là endpoint công khai (khách không có tài khoản) nên mọi số tiền đều
 * do server tự tính từ CSDL, không nhận giá gửi lên từ trình duyệt.
 */
class CheckoutController extends Controller
{
    /** Mã BIN NAPAS của các mã ngân hàng thường được dùng trong cài đặt. */
    private const VIETQR_BANK_BINS = [
        'MB' => '970422', 'MBBANK' => '970422',
        'VCB' => '970436', 'VIETCOMBANK' => '970436',
        'BIDV' => '970418',
        'CTG' => '970415', 'VIETINBANK' => '970415',
        'TCB' => '970407', 'TECHCOMBANK' => '970407',
        'VPB' => '970432', 'VPBANK' => '970432',
        'ACB' => '970416',
        'TPB' => '970423', 'TPBANK' => '970423',
        'STB' => '970403', 'SACOMBANK' => '970403',
        'AGR' => '970405', 'AGRIBANK' => '970405',
        'HDB' => '970437', 'HDBANK' => '970437',
        'VIB' => '970441',
        'SHB' => '970443',
        'MSB' => '970426',
        'OCB' => '970448',
    ];

    /**
     * Khớp mã hình thức thanh toán mà web bán hàng gửi lên với khoá trong cài đặt.
     * Mọi mã lạ đều coi là chuyển khoản, đúng như mặc định của trường payment_method.
     */
    private function settingKey(?string $paymentMethod): string
    {
        return $paymentMethod === 'cod' ? 'cod' : 'bank_transfer';
    }

    /**
     * Báo giá và kiểm tồn từ dữ liệu hiện tại, trước khi khách xác nhận đơn.
     * Không giữ hàng hay tạo Invoice; store() vẫn kiểm lại dưới khóa dòng.
     */
    public function quote(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer', 'banking'])],
            'items' => 'required|array|min:1|max:100',
            'items.*.variant_id' => 'required|integer|min:1',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'voucher_code' => 'nullable|string|max:50|regex:/^[A-Za-z0-9_-]+$/',
        ]);

        $settings = Setting::sales();
        $methodKey = $this->settingKey($data['payment_method']);
        if (empty($settings[$methodKey]['enabled'])) {
            return response()->json([
                'success' => false,
                'error' => 'Hình thức thanh toán này hiện không nhận đơn.',
            ], 422);
        }

        $wanted = [];
        foreach ($data['items'] as $item) {
            $id = (int) $item['variant_id'];
            $wanted[$id] = ($wanted[$id] ?? 0) + (int) $item['quantity'];
            if ($wanted[$id] > 100) {
                return response()->json([
                    'success' => false,
                    'error' => 'Số lượng một mẫu sản phẩm không được vượt quá 100.',
                ], 422);
            }
        }

        $variants = ProductVariant::with('product')
            ->whereIn('id', array_keys($wanted))->get()->keyBy('id');
        $items = [];
        $subtotal = 0;

        foreach ($wanted as $id => $quantity) {
            $variant = $variants->get($id);
            if (!$variant || !$variant->product || $variant->product->trashed()
                || (int) $variant->product->status === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Một sản phẩm trong giỏ không còn được bán.',
                ], 422);
            }

            $available = (int) $variant->quantity;
            if ($variant->product->manage_stock && $available < $quantity) {
                return response()->json([
                    'success' => false,
                    'error' => 'Sản phẩm "' . $variant->product->product_name . '" ('
                        . $variant->label . ') chỉ còn ' . $available . ' sản phẩm.',
                    'variant_id' => $id,
                    'available' => $available,
                ], 422);
            }

            $price = $variant->selling_price;
            $lineTotal = $price * $quantity;
            $subtotal += $lineTotal;
            $items[] = [
                'variant_id' => $id,
                'product' => $variant->product->product_name,
                'label' => $variant->label,
                'quantity' => $quantity,
                'unit_price' => $price,
                'line_total' => $lineTotal,
                'available' => $available,
                'manage_stock' => (bool) $variant->product->manage_stock,
            ];
        }

        $shippingFee = Setting::shippingFeeFor($methodKey, array_sum($wanted), $settings);
        $discount = 0;
        $voucherCode = isset($data['voucher_code']) ? strtoupper(trim((string) $data['voucher_code'])) : null;

        if ($voucherCode) {
            $voucher = Voucher::where('code', $voucherCode)->first();
            if (! $voucher) {
                return response()->json(['success' => false, 'error' => 'Mã giảm giá không hợp lệ.'], 422);
            }

            $voucherQuote = $voucher->quote($subtotal, $shippingFee);
            if (isset($voucherQuote['error'])) {
                return response()->json(['success' => false, 'error' => $voucherQuote['error']], 422);
            }

            $discount = (int) $voucherQuote['discount'];
            $shippingFee = (int) $voucherQuote['shipping'];
        }

        return response()->json([
            'success' => true,
            'ok' => true,
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_fee' => $shippingFee,
            'total_amount' => $subtotal + $shippingFee - $discount,
        ]);
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
            // `banking` là mã của luồng PayOS cũ; `bank_transfer` là chuyển
            // khoản thủ công từ web bán hàng hiện tại.
            'payment_method' => ['nullable', Rule::in(['cod', 'bank_transfer', 'banking'])],
            'expected_total_amount' => 'nullable|integer|min:0',
            'checkout_ref' => 'nullable|uuid',
            // Mã này chỉ đến từ route handler của storefront (sau bí mật dùng
            // chung). Giá trị giảm vẫn được dựng lại ở transaction bên dưới.
            'voucher_code' => 'nullable|string|max:50|regex:/^[A-Za-z0-9_-]+$/',
            // Chỉ StorefrontOrderController gọi nội bộ sau khi PayOS xác nhận. Nó phải
            // khớp với khóa QR tạm của từng mẫu in để không nhận tiền trùng hai lần.
            'storefront_ref' => 'nullable|string|max:32|regex:/^[A-Za-z0-9]+$/',

            /*
             * `items` được phép rỗng KHI đơn có mã thiết kế in: khách đặt in áo
             * thường không mua kèm hàng bán sẵn, và bắt họ thêm một món vô nghĩa
             * chỉ để đơn hợp lệ là bắt sai chỗ.
             */
            'items' => 'present|array|max:100',
            'items.*.variant_id' => 'required|integer|min:1',
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

        if (! config('features.print_studio') && ! empty($data['print_design_codes'])) {
            return response()->json([
                'success' => false,
                'error' => 'Tính năng in theo yêu cầu chưa được bật cho cửa hàng này.',
            ], 422);
        }

        $codes = array_values(array_unique($data['print_design_codes'] ?? []));
        $wanted = [];
        foreach ($data['items'] as $item) {
            $id = (int) $item['variant_id'];
            $wanted[$id] = ($wanted[$id] ?? 0) + (int) $item['quantity'];
        }
        ksort($wanted, SORT_NUMERIC);

        $fingerprintCodes = $codes;
        sort($fingerprintCodes);
        $fingerprint = hash('sha256', json_encode([
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null,
            'province' => $data['province'],
            'ward' => $data['ward'],
            'address' => $data['address'],
            'note' => $data['note'] ?? null,
            'payment_method' => $data['payment_method'] ?? 'banking',
            'voucher_code' => isset($data['voucher_code'])
                ? strtoupper(trim((string) $data['voucher_code']))
                : null,
            'items' => $wanted,
            'print_design_codes' => $fingerprintCodes,
            'storefront_ref' => $data['storefront_ref'] ?? null,
            'refund_bank_name' => $data['refund_bank_name'] ?? null,
            'refund_account_number' => $data['refund_account_number'] ?? null,
            'refund_account_name' => $data['refund_account_name'] ?? null,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        if (! empty($data['checkout_ref'])) {
            $existing = Invoice::withTrashed()->orders()
                ->where('checkout_ref', $data['checkout_ref'])->first();
            if ($existing) {
                return $this->existingCheckoutResponse($existing, $fingerprint);
            }
        }

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

        // Phí giao hàng lấy từ cài đặt bán hàng, không phải hằng số trong mã nguồn.
        $paymentMethod = $this->settingKey($data['payment_method'] ?? 'bank_transfer');
        $methodKey = $paymentMethod;
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
        $shippingBeforeVoucher = $shippingFee;

        DB::beginTransaction();
        try {
            // Khoá các dòng biến thể để hai khách đặt cùng lúc không bán quá tồn.
            $variants = ProductVariant::with([
                'product' => fn ($query) => $query->storefrontVisible(),
            ])
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

                if (!$variant || !$variant->product || $variant->product->trashed()
                    || (int) $variant->product->status === 0) {
                    throw new \RuntimeException('Sản phẩm không còn tồn tại.');
                }

                if ($quantity > 100) {
                    throw new \RuntimeException('Số lượng một mẫu sản phẩm không được vượt quá 100.');
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

            $discount = 0;
            $voucherCode = isset($data['voucher_code']) ? strtoupper(trim((string) $data['voucher_code'])) : null;
            if ($voucherCode) {
                // Khoá đúng voucher trước khi kiểm lượt dùng. Hai khách thanh
                // toán cùng mã giới hạn không thể cùng vượt qua lượt cuối.
                $voucher = Voucher::where('code', $voucherCode)->lockForUpdate()->first();
                if (! $voucher) {
                    throw new \RuntimeException('Mã giảm giá không hợp lệ.');
                }

                $quote = $voucher->quote($subtotal + $printFee, $shippingFee);
                if (isset($quote['error'])) {
                    throw new \RuntimeException($quote['error']);
                }

                $discount = (int) $quote['discount'];
                $shippingFee = (int) $quote['shipping'];
                // Miễn phí vận chuyển là shop chịu thêm phần khách đáng lẽ trả.
                if ($shippingFee < $shippingBeforeVoucher) {
                    $shopShippingFee += $shippingBeforeVoucher - $shippingFee;
                }
            }

            $totalAmount = $subtotal + $printFee + $shippingFee - $discount;
            if (isset($data['expected_total_amount'])
                && (int) $data['expected_total_amount'] !== $totalAmount) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'error' => 'Giá, khuyến mãi hoặc phí giao hàng đã thay đổi. Vui lòng kiểm tra lại đơn.',
                    'discount' => $discount,
                    'shipping_fee' => $shippingFee,
                    'total_amount' => $totalAmount,
                ], 409);
            }

            $customer = Customer::mergeByPhone([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'address' => $data['address'],
                'province' => $data['province'],
                'ward' => $data['ward'],
            ]);

            $note = $data['note'] ?? null;
            if ($voucherCode) {
                $note = trim(($note ? $note . "\n" : '') . "Voucher: {$voucherCode}");
            }

            $invoiceData = [
                'invoice_type' => Invoice::TYPE_ORDER,
                'order_code' => $this->generateOrderCode(),
                'checkout_ref' => $data['checkout_ref'] ?? null,
                'checkout_fingerprint' => ! empty($data['checkout_ref']) ? $fingerprint : null,
                'order_status' => Invoice::STATUS_PENDING,
                'customer_id' => $customer->id,
                'user_id' => $this->systemUserId(),
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'shipping_fee' => $shippingFee,
                // Khoản shop tự gánh: không cộng vào tiền khách trả, nhưng vẫn phải
                // lưu lại, nếu không thì lúc tính lãi khoản này biến mất.
                'shop_shipping_fee' => $shopShippingFee,
                'shipping_name' => $data['customer_name'],
                'shipping_phone' => $customer->customer_phone,
                'shipping_address' => implode(', ', [$data['address'], $data['ward'], $data['province']]),
                'payment_method' => $paymentMethod,
                // Chuyển khoản nhận qua SePay chỉ giữ trạng thái chờ trong một
                // khoảng ngắn; chưa trừ tồn cho tới khi SePay xác nhận tiền về.
                'payment_expires_at' => $paymentMethod === 'bank_transfer'
                    ? now()->addMinutes((int) config('services.storefront.payment_window_minutes', 15))
                    : null,
                // Chưa nhận được tiền: đơn chỉ được xác nhận sau khi chuyển khoản thành công.
                'pay_status' => 0,
                'note' => $note,
                'signature_name' => $data['customer_name'],
            ];

            // Các cột này thuộc migration của studio in. Khi studio bị tắt, core
            // checkout phải chạy độc lập và tuyệt đối không đòi schema của module đó.
            if (config('features.print_studio')) {
                $invoiceData['print_fee'] = $printFee;
                $invoiceData['refund_bank_name'] = $data['refund_bank_name'] ?? null;
                $invoiceData['refund_account_number'] = $data['refund_account_number'] ?? null;
                $invoiceData['refund_account_name'] = $data['refund_account_name'] ?? null;
            }

            $invoice = Invoice::create($invoiceData);

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

            }

            foreach ($printDesigns as $printDesign) {
                // Chỉ đến đây mẫu mới thuộc một đơn thật. `store()` chỉ được gọi sau
                // khi PayOS đã xác nhận, nên chuyển draft → pending tại đây là lúc duy nhất
                // nó được phép vào hàng đợi duyệt. `invoice_id` cũng chặn mẫu được đặt hai lần.
                $printDesign->update([
                    'invoice_id' => $invoice->id,
                    'pending_payment_ref' => null,
                    'review_status' => PrintDesign::STATUS_PENDING,
                ]);

            }

            // COD giữ hàng ngay lúc khách chốt đơn. Với chuyển khoản, mã QR chỉ
            // tạo đơn chờ; webhook SePay sẽ gọi cùng hàm này sau khi nhận tiền.
            if ($paymentMethod === 'cod') {
                $invoice->deductStockLines();
                $invoice->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_code' => $invoice->order_code,
                'payment_reference' => $this->paymentReference((string) $invoice->order_code),
                // Ảnh được dựng trong chính response tạo đơn. Nếu để landing gọi
                // thêm endpoint QR, khách phải chờ thêm một vòng Render -> API
                // dù tất cả dữ liệu tạo ảnh đã có ngay tại đây.
                'payment_qr_data_uri' => $paymentMethod === 'bank_transfer'
                    ? $this->paymentQrDataUri($invoice)
                    : null,
                'subtotal' => $subtotal,
                'print_fee' => $printFee,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'order_status' => $invoice->order_status,
                'pay_status' => (int) $invoice->pay_status,
                'message' => $paymentMethod === 'cod'
                    ? 'Đã nhận đơn hàng COD. Cửa hàng sẽ liên hệ xác nhận trước khi giao.'
                    : 'Đã nhận đơn hàng. Đơn sẽ được xác nhận sau khi nhận được chuyển khoản.',
                'discount' => $discount,
            ], 201);
        } catch (\RuntimeException $e) {
            DB::rollBack();
            // Lỗi nghiệp vụ (hết hàng, sản phẩm đã xoá): nói rõ cho khách biết.
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (! empty($data['checkout_ref'])) {
                // Hai request cùng ref có thể cùng vượt qua lần đọc đầu. Ràng
                // buộc unique chặn bản ghi thứ hai, sau đó trả đơn đã commit.
                $existing = Invoice::withTrashed()->orders()
                    ->where('checkout_ref', $data['checkout_ref'])->first();
                if ($existing) {
                    return $this->existingCheckoutResponse($existing, $fingerprint);
                }
            }
            Log::error('Nhận đơn từ web bán hàng thất bại: ' . $e->getMessage());
            // Không lộ chi tiết lỗi hệ thống ra ngoài.
            return response()->json([
                'success' => false,
                'error' => 'Chưa thể tạo đơn hàng. Đơn chưa được ghi nhận; vui lòng kiểm tra kết nối rồi thử lại.',
            ], 500);
        }
    }

    private function existingCheckoutResponse(Invoice $invoice, string $fingerprint)
    {
        if ($invoice->trashed() || ! hash_equals((string) $invoice->checkout_fingerprint, $fingerprint)) {
            return response()->json([
                'success' => false,
                'error' => 'Mã yêu cầu đã được dùng cho một đơn khác.',
            ], 409);
        }

        $invoice->loadMissing('productInvoices');

        return response()->json([
            'success' => true,
            'already_created' => true,
            'order_code' => $invoice->order_code,
            'payment_reference' => $this->paymentReference((string) $invoice->order_code),
            'payment_qr_data_uri' => $invoice->payment_method === 'bank_transfer'
                ? $this->paymentQrDataUri($invoice)
                : null,
            'subtotal' => $invoice->subtotal,
            'print_fee' => (int) ($invoice->print_fee ?? 0),
            'shipping_fee' => (int) $invoice->shipping_fee,
            'total_amount' => (int) $invoice->total_amount,
            'order_status' => $invoice->order_status,
            'pay_status' => (int) $invoice->pay_status,
            'message' => 'Đơn hàng đã được ghi nhận trước đó.',
        ]);
    }

    public function status(string $checkoutRef)
    {
        if (!Str::isUuid($checkoutRef)) {
            return response()->json(['error' => 'Mã yêu cầu không hợp lệ.'], 422);
        }

        $order = Invoice::orders()->where('checkout_ref', $checkoutRef)->first();
        if (!$order) {
            return response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
        }

        return response()->json([
            'success' => true,
            'order_code' => $order->order_code,
            'payment_reference' => $this->paymentReference((string) $order->order_code),
            'order_status' => $order->order_status,
            'pay_status' => (int) $order->pay_status,
        ]);
    }

    /**
     * Dựng ảnh VietQR trên chính máy chủ QLBH từ tổng tiền và mã đơn đã tạo.
     * Không gọi dịch vụ QR bên thứ ba, nên số tài khoản và nội dung đơn không
     * rời khỏi hệ thống trước khi ảnh được trả về landing qua route có bí mật.
     */
    public function paymentQr(string $checkoutRef)
    {
        if (! Str::isUuid($checkoutRef)) {
            return response()->json(['error' => 'Mã yêu cầu không hợp lệ.'], 422);
        }

        $invoice = Invoice::orders()->where('checkout_ref', $checkoutRef)->first();
        if (! $invoice) {
            return response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
        }

        $image = $this->paymentQrImage($invoice);
        if ($image === null) {
            return response()->json([
                'error' => 'Chưa cấu hình đủ tài khoản nhận chuyển khoản để tạo mã VietQR.',
            ], 422);
        }

        return response($image, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    /** Dùng cho response checkout để landing không cần gọi thêm endpoint ảnh QR. */
    private function paymentQrDataUri(Invoice $invoice): ?string
    {
        $image = $this->paymentQrImage($invoice);

        return $image === null ? null : 'data:image/png;base64,' . base64_encode($image);
    }

    /** Dựng PNG QR từ dữ liệu đã có trong đơn, không gọi một dịch vụ bên ngoài. */
    private function paymentQrImage(Invoice $invoice): ?string
    {
        $bank = (array) data_get(Setting::sales(), 'bank_transfer.bank', []);
        $bankBin = $this->vietQrBankBin((string) ($bank['code'] ?? ''));
        $accountNumber = preg_replace('/\D+/', '', (string) ($bank['account_number'] ?? ''));

        if ($bankBin === null || $accountNumber === '' || strlen($accountNumber) > 19) {
            return null;
        }

        $payload = $this->vietQrPayload(
            $bankBin,
            $accountNumber,
            max(0, (int) $invoice->total_amount),
            $this->paymentReference((string) $invoice->order_code),
        );

        $qrCode = new QrCode($payload);
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setSize(560);
        $qrCode->setMargin(12);

        return (new PngWriter())->write($qrCode)->getString();
    }

    private function vietQrBankBin(string $code): ?string
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));

        if (preg_match('/^\d{6}$/', $normalized)) {
            return $normalized;
        }

        return self::VIETQR_BANK_BINS[$normalized] ?? null;
    }

    /** Tạo payload QRIBFTTA theo chuẩn VietQR/EMVCo, với CRC-16/CCITT-FALSE. */
    private function vietQrPayload(string $bankBin, string $accountNumber, int $amount, string $reference): string
    {
        $tlv = static fn (string $id, string $value): string => $id . str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT) . $value;
        $beneficiary = $tlv('00', $bankBin) . $tlv('01', $accountNumber);
        $merchant = $tlv('00', 'A000000727') . $tlv('01', $beneficiary) . $tlv('02', 'QRIBFTTA');
        $purpose = substr($reference, 0, 25);

        $payload = $tlv('00', '01')
            . $tlv('01', '12')
            . $tlv('38', $merchant)
            . $tlv('53', '704')
            . $tlv('54', (string) $amount)
            . $tlv('58', 'VN')
            . $tlv('62', $tlv('08', $purpose))
            . '6304';

        return $payload . $this->crc16Ccitt($payload);
    }

    /**
     * VietinBank chỉ đẩy biến động cho SePay khi nội dung bắt đầu bằng SEVQR.
     * Mã đơn RUNGU phía sau được webhook trích xuất để khớp đúng invoice.
     */
    private function paymentReference(string $paymentCode): string
    {
        return substr('SEVQR ' . $paymentCode, 0, 25);
    }

    private function crc16Ccitt(string $value): string
    {
        $crc = 0xFFFF;
        for ($index = 0, $length = strlen($value); $index < $length; $index++) {
            $crc ^= ord($value[$index]) << 8;
            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) !== 0 ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
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

            try {
                $order->deductStockLines();
            } catch (\RuntimeException $e) {
                $order->payment_expires_at = null;
                $order->note = trim(($order->note ? $order->note . "\n" : '')
                    . 'CẦN XỬ LÝ: đã ghi nhận thanh toán nhưng ' . $e->getMessage());
                $order->save();
                app(VoucherRedemption::class)->recordPaidOrder($order);
                DB::commit();

                Log::critical('Đã nhận tiền nhưng không thể trừ tồn.', [
                    'order_code' => $orderCode,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => true,
                    'stock_issue' => true,
                    'message' => 'Đã ghi nhận thanh toán; nhân viên sẽ xử lý vì tồn kho vừa thay đổi.',
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
            app(VoucherRedemption::class)->recordPaidOrder($order);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Đã ghi nhận thanh toán.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Ghi nhận thanh toán thất bại: ' . $e->getMessage(), ['order_code' => $orderCode]);

            return response()->json([
                'error' => 'Chưa thể ghi nhận thanh toán. Nếu bạn đã chuyển khoản, vui lòng chờ cửa hàng đối soát trước khi thử lại.',
            ], 500);
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

        // Huỷ đơn chuyển khoản quá hạn trước khi kiểm tồn. Các đơn mới chưa trừ
        // tồn khi tạo QR nên thao tác này chỉ đổi trạng thái đơn, không hoàn kho.
        Invoice::cancelExpiredHolds();

        $variants = ProductVariant::with([
            'product' => fn ($query) => $query->storefrontVisible(),
        ])
            ->whereIn('id', array_column($data['items'], 'variant_id'))
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($data['items'] as $item) {
            $variant = $variants->get((int) $item['variant_id']);
            // Sản phẩm thuộc danh mục đã tắt cũng được coi là không còn bán,
            // kể cả khi khách giữ một giỏ cũ hoặc tự gọi API kiểm tra kho.
            $available = $variant?->product ? $variant->quantity : 0;
            // Hàng không theo dõi tồn kho luôn đủ: số tồn của nó chỉ để tham khảo.
            $unlimited = (bool) $variant && ! $variant->product?->manage_stock;

            $result[] = [
                'variant_id' => (int) $item['variant_id'],
                'available' => $available,
                'manage_stock' => ! $unlimited,
                'enough' => $unlimited || $available >= (int) $item['quantity'],
                'product' => $variant?->product?->product_name,
                'label' => $variant?->label,
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
            $code = $this->paymentCodePrefix() . now()->format('ymd')
                . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Invoice::withTrashed()->where('order_code', $code)->exists());

        return $code;
    }

    private function paymentCodePrefix(): string
    {
        $prefix = strtoupper((string) config('services.sepay.payment_prefix', 'RUNGU'));
        if (! preg_match('/^[A-Z]{2,5}$/', $prefix)) {
            $prefix = 'RUNGU';
        }

        return $prefix;
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
