<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Bán hàng tại quầy.
 *
 * Khác đơn đặt trên web: khách cầm hàng về ngay và trả tiền ngay, nên đơn được
 * lập thẳng ở trạng thái "Hoàn thành" và "Đã thanh toán", không có địa chỉ giao
 * và không đi qua các bước đóng gói / vận chuyển.
 *
 * Muốn trả hàng thì vào trang Đơn hàng chuyển sang "Hoàn hàng", lúc đó tồn kho
 * tự được cộng trả lại.
 */
class PosController extends Controller
{
    /** Cách khách trả tiền tại quầy. */
    public const PAYMENT_METHODS = [
        'cash' => 'Tiền mặt',
        'banking' => 'Chuyển khoản',
        'card' => 'Quẹt thẻ',
    ];

    public function index()
    {
        return view('pos.index', [
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    /**
     * Tìm biến thể còn hàng theo tên sản phẩm hoặc SKU, để nhân viên chọn nhanh.
     */
    public function search(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));

        $query = ProductVariant::with('product')
            ->whereHas('product', fn($q) => $q->where('status', '!=', 0))
            ->where('quantity', '>', 0);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('sku', 'ilike', "%{$keyword}%")
                    ->orWhereHas('product', fn($p) => $p->where('product_name', 'ilike', "%{$keyword}%"));
            });
        }

        $variants = $query->orderByDesc('quantity')->limit(30)->get();

        return response()->json($variants->map(fn($v) => [
            'id' => $v->id,
            'product' => $v->product->product_name,
            'label' => $v->label,
            'sku' => $v->sku,
            'stock' => (int) $v->quantity,
            'price' => (int) ($v->price_override ?? $v->product->discount_price ?? $v->product->sell_price),
        ])->values());
    }

    /**
     * Lập đơn bán tại quầy.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',

            // Khách vãng lai thì bỏ trống hết, đơn ghi là "Khách lẻ".
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',

            'payment_method' => ['required', Rule::in(array_keys(self::PAYMENT_METHODS))],
            'discount' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        // Gộp dòng trùng biến thể để không trừ kho hai lần cho cùng một món.
        $wanted = [];
        foreach ($data['items'] as $item) {
            $id = (int) $item['variant_id'];
            $wanted[$id] = ($wanted[$id] ?? 0) + (int) $item['quantity'];
        }

        DB::beginTransaction();
        try {
            // Khoá dòng biến thể: quầy và web có thể bán trùng một món cùng lúc.
            $variants = ProductVariant::with('product')
                ->whereIn('id', array_keys($wanted))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0;

            foreach ($wanted as $variantId => $quantity) {
                $variant = $variants->get($variantId);

                if (!$variant || !$variant->product) {
                    throw new \RuntimeException('Sản phẩm không còn tồn tại.');
                }

                if ($variant->quantity < $quantity) {
                    throw new \RuntimeException(
                        $variant->product->product_name . ' (' . $variant->label
                        . ') chỉ còn ' . $variant->quantity . ' sản phẩm.'
                    );
                }

                // Giá lấy từ CSDL, không nhận giá gửi lên từ trình duyệt.
                $unitPrice = (int) ($variant->price_override
                    ?? $variant->product->discount_price
                    ?? $variant->product->sell_price);

                $lines[] = ['variant' => $variant, 'quantity' => $quantity, 'unit_price' => $unitPrice];
                $subtotal += $unitPrice * $quantity;
            }

            $discount = min((int) ($data['discount'] ?? 0), $subtotal);

            // Có số điện thoại thì gộp vào hồ sơ khách để còn thống kê khách quen.
            $customer = null;
            if (!empty($data['customer_phone'])) {
                $customer = Customer::mergeByPhone([
                    'customer_name' => $data['customer_name'] ?: 'Khách lẻ',
                    'customer_phone' => $data['customer_phone'],
                ]);
            }

            $invoice = Invoice::create([
                'invoice_type' => Invoice::TYPE_ORDER,
                'order_code' => $this->generateOrderCode(),
                // Khách cầm hàng về ngay: đơn xong luôn, không qua đóng gói/giao hàng.
                'order_status' => Invoice::STATUS_COMPLETED,
                'pay_status' => 1,
                'customer_id' => $customer?->id,
                'user_id' => $request->user()->id,
                'total_amount' => $subtotal - $discount,
                'discount' => $discount,
                'shipping_fee' => 0,
                'payment_method' => $data['payment_method'],
                'note' => $data['note'] ?? null,
                'signature_name' => $request->user()->name,
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

                $line['variant']->quantity -= $line['quantity'];
                $line['variant']->save();
            }

            foreach (array_unique(array_map(fn($l) => $l['variant']->product_id, $lines)) as $productId) {
                $total = ProductVariant::where('product_id', $productId)->sum('quantity');
                Product::where('id', $productId)->update(['status' => $total > 0 ? 1 : 2]);
            }

            DB::commit();

            return response()->json([
                'success' => 'Đã lập đơn ' . $invoice->order_code . '.',
                'order_code' => $invoice->order_code,
                'total_amount' => $invoice->total_amount,
                'print_url' => '/order/' . $invoice->id . '/print',
            ]);
        } catch (\RuntimeException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lập đơn tại quầy thất bại: ' . $e->getMessage());
            return response()->json(['error' => 'Không lập được đơn: ' . $e->getMessage()], 500);
        }
    }

    private function generateOrderCode(): string
    {
        do {
            // Tiền tố khác đơn web để nhìn mã là biết đơn bán tại quầy.
            $code = 'BQ' . now()->format('ymd') . strtoupper(Str::random(4));
        } while (Invoice::withTrashed()->where('order_code', $code)->exists());

        return $code;
    }
}
