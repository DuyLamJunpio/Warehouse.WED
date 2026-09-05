<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PrintDesign;
use App\Models\StorefrontOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Chỗ lưu đơn cho trang thanh toán của web bán hàng.
 *
 * Web bán hàng vốn cất những đơn này vào một tệp JSON trên đĩa máy chủ nó chạy.
 * Trên Vercel thư mục duy nhất ghi được là thư mục tạm của máy ảo, mà máy ảo bị
 * thay sau vài phút: đơn mất trắng, khách đang quét mã QR thì trang đơn hàng
 * thành 404 dù đơn đã vào sổ bên kho. Kho là chỗ duy nhất trong hệ thống này có
 * cơ sở dữ liệu thật, nên nó giữ luôn.
 *
 * Không endpoint nào ở đây được để lộ ra ngoài: `payload` có tên, số điện thoại
 * và địa chỉ của khách. Cả nhóm nằm sau middleware `storefront.secret`.
 */
class StorefrontOrderController extends Controller
{
    public function __construct(private CheckoutController $checkout)
    {
    }

    /**
     * Lưu một đơn mới.
     *
     * Dùng updateOrCreate theo `ref` để gọi lại không sinh bản ghi thứ hai: web
     * bán hàng có thể thử lại sau một lần mạng hỏng giữa đường, và lúc đó đơn
     * đã nằm đây rồi.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Chữ và số, đúng bộ ký tự web bán hàng sinh mã.
            'ref' => 'required|string|max:32|regex:/^[A-Za-z0-9]+$/',
            'order_code' => 'required|integer|min:1',
            'payload' => 'required|array',
        ]);

        /*
         * Mã PayOS phải là duy nhất suốt đời tài khoản người bán, nên một mã
         * đang thuộc đơn khác là lỗi thật chứ không phải chuyện ghi đè được:
         * mọi giao dịch báo về theo mã đó sẽ khớp sai đơn.
         */
        $clash = StorefrontOrder::where('order_code', $data['order_code'])
            ->where('ref', '!=', $data['ref'])
            ->exists();

        if ($clash) {
            return response()->json([
                'error' => 'Mã thanh toán này đã thuộc một đơn khác.',
            ], 409);
        }

        $result = DB::transaction(function () use ($data) {
            $reservationError = $this->reservePrintDesigns($data['payload'], $data['ref']);
            if ($reservationError) {
                return ['error' => $reservationError];
            }

            $order = StorefrontOrder::updateOrCreate(
                ['ref' => $data['ref']],
                ['order_code' => $data['order_code'], 'payload' => $data['payload']],
            );

            return ['payload' => $order->payload];
        });

        if (! empty($result['error'])) {
            return response()->json(['error' => $result['error']], 409);
        }

        StorefrontOrder::prune();

        return response()->json($result['payload'], 201);
    }

    /**
     * Giữ một mẫu in cho đúng một QR còn hiệu lực, nhưng không tạo Invoice hay
     * thông báo nào cho nhân viên. Nếu QR cũ đã hết hạn, mẫu chuyển sang phiên mới.
     */
    private function reservePrintDesigns(array $payload, string $ref): ?string
    {
        $codes = collect((array) data_get($payload, 'cart.prints', []))
            ->pluck('code')
            ->filter(fn ($code) => is_string($code) && $code !== '')
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            return null;
        }

        $designs = PrintDesign::whereIn('code', $codes)->lockForUpdate()->get()->keyBy('code');
        if ($designs->count() !== $codes->count()) {
            return 'Một mẫu in trong giỏ không còn tồn tại. Vui lòng thiết kế lại.';
        }

        foreach ($codes as $code) {
            /** @var PrintDesign $design */
            $design = $designs->get($code);

            if ($design->invoice_id) {
                return 'Mẫu ' . $code . ' đã được đặt trong một đơn khác.';
            }

            $previousRef = $design->pending_payment_ref;
            if (! $previousRef || $previousRef === $ref) {
                if ($previousRef !== $ref) {
                    $design->pending_payment_ref = $ref;
                    $design->save();
                }
                continue;
            }

            $previous = StorefrontOrder::where('ref', $previousRef)->lockForUpdate()->first();
            $previousPayload = (array) ($previous?->payload ?? []);
            if (($previousPayload['status'] ?? null) === 'PAID') {
                return 'Mẫu ' . $code . ' đang được hoàn tất sau một thanh toán trước đó.';
            }

            if ($previous && $this->paymentIsOpen($previousPayload)) {
                return 'Mẫu ' . $code . ' đang chờ thanh toán qua một mã QR khác.';
            }

            // QR cũ hết hạn hoặc bản ghi đã được dọn: nhận giữ cho phiên mới.
            $design->pending_payment_ref = $ref;
            $design->save();
        }

        return null;
    }

    private function paymentIsOpen(array $payload): bool
    {
        $status = $payload['status'] ?? null;
        $expiresAt = (int) ($payload['expiresAt'] ?? 0);

        return in_array($status, ['PENDING', 'PROCESSING', 'UNDERPAID'], true)
            && $expiresAt > now()->getTimestampMs();
    }

    /** Đơn khách đang mở, tra theo mã trên URL. */
    public function show(string $ref)
    {
        $order = StorefrontOrder::where('ref', $ref)->first();

        return $order
            ? response()->json($order->payload)
            : response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
    }

    /** Tra theo mã PayOS — webhook chỉ gửi mã này, không gửi mã trên URL. */
    public function showByCode(string $orderCode)
    {
        $order = StorefrontOrder::where('order_code', (int) $orderCode)->first();

        return $order
            ? response()->json($order->payload)
            : response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
    }

    /**
     * Tạo đơn quản trị sau khi PayOS đã xác nhận thanh toán.
     *
     * Webhook và trang thanh toán có thể cùng nhìn thấy trạng thái PAID. Khoá
     * dòng StorefrontOrder giúp chúng gọi cùng lúc vẫn chỉ sinh duy nhất một
     * Invoice và một lần đưa mẫu in vào hàng đợi duyệt.
     */
    public function fulfill(string $ref)
    {
        try {
            $result = DB::transaction(function () use ($ref) {
                $order = StorefrontOrder::where('ref', $ref)->lockForUpdate()->first();
                if (! $order) {
                    return ['status' => 404, 'body' => ['error' => 'Không tìm thấy đơn hàng.']];
                }

                $payload = $order->payload;
                if (($payload['status'] ?? null) !== 'PAID') {
                    return ['status' => 409, 'body' => ['error' => 'Đơn chưa được xác nhận thanh toán.']];
                }

                if (! empty($payload['warehouseOrderCode'])) {
                    return [
                        'status' => 200,
                        'body' => [
                            'order_code' => $payload['warehouseOrderCode'],
                            'already_fulfilled' => true,
                        ],
                    ];
                }

                // Tái dùng đúng bộ máy tạo Invoice, kiểm kho và gắn mẫu hiện có.
                // Request này không đi qua route công khai: dữ liệu lấy từ payload nội bộ đã
                // lưu và API này đã nằm sau `storefront.secret`.
                $created = $this->checkout->store(
                    Request::create('/api/checkout', 'POST', $this->checkoutPayload($payload)),
                );
                $createdBody = $created->getData(true);
                if ($created->getStatusCode() >= 400) {
                    return [
                        'status' => $created->getStatusCode(),
                        'body' => ['error' => $createdBody['error'] ?? 'Không tạo được đơn hàng.'],
                    ];
                }

                $orderCode = (string) ($createdBody['order_code'] ?? '');
                if ($orderCode === '') {
                    throw new \RuntimeException('Checkout không trả về mã đơn quản trị.');
                }

                $paid = $this->checkout->markPaid($orderCode);
                if ($paid->getStatusCode() >= 400) {
                    $paidBody = $paid->getData(true);
                    throw new \RuntimeException($paidBody['error'] ?? 'Không ghi nhận được thanh toán.');
                }

                $payload['warehouseOrderCode'] = $orderCode;
                $order->payload = $payload;
                $order->save();

                return ['status' => 201, 'body' => ['order_code' => $orderCode]];
            });

            return response()->json($result['body'], $result['status']);
        } catch (\Throwable $error) {
            Log::error('Hoàn tất đơn web đã thanh toán thất bại.', [
                'ref' => $ref,
                'error' => $error->getMessage(),
            ]);

            return response()->json(['error' => 'Không ghi nhận được đơn hàng đã thanh toán.'], 500);
        }
    }

    /** Nắn payload nội bộ của Next thành hình dạng CheckoutController nhận. */
    private function checkoutPayload(array $payload): array
    {
        $customer = (array) ($payload['customer'] ?? []);
        $cart = (array) ($payload['cart'] ?? []);
        $refund = (array) ($payload['refund'] ?? []);

        return [
            'customer_name' => (string) ($customer['fullName'] ?? ''),
            'customer_phone' => (string) ($customer['phone'] ?? ''),
            'customer_email' => (string) ($customer['email'] ?? ''),
            'province' => (string) ($customer['city'] ?? ''),
            'ward' => (string) ($customer['ward'] ?? ''),
            'address' => (string) ($customer['address'] ?? ''),
            'note' => (string) ($customer['note'] ?? ''),
            'payment_method' => ($payload['paymentMethod'] ?? null) === 'cod' ? 'cod' : 'banking',
            'storefront_ref' => (string) ($payload['ref'] ?? ''),
            'refund_bank_name' => (string) ($refund['bankName'] ?? ''),
            'refund_account_number' => (string) ($refund['accountNumber'] ?? ''),
            'refund_account_name' => (string) ($refund['accountName'] ?? ''),
            'items' => collect((array) ($cart['lines'] ?? []))
                ->map(fn (array $line) => [
                    'variant_id' => (int) ($line['id'] ?? 0),
                    'quantity' => (int) ($line['qty'] ?? 0),
                ])
                ->values()
                ->all(),
            'print_design_codes' => collect((array) ($cart['prints'] ?? []))
                ->pluck('code')
                ->filter(fn ($code) => is_string($code) && $code !== '')
                ->values()
                ->all(),
        ];
    }

    /**
     * Ghi một phần thay đổi vào đơn.
     *
     * Hoà ở mức khoá ngoài cùng, đúng như phía web bán hàng vẫn làm, và nằm
     * trong một transaction có khoá dòng: webhook PayOS và vòng poll của trang
     * thanh toán thường chạy sát nhau, đọc rồi ghi rời nhau là một bên xoá mất
     * phần bên kia vừa ghi.
     */
    public function update(Request $request, string $ref)
    {
        // present chứ không required: `required` đánh trượt cả mảng rỗng, mà một
        // patch rỗng chỉ nên là một lần ghi không đổi gì.
        $data = $request->validate(['patch' => 'present|array']);

        $payload = DB::transaction(function () use ($ref, $data) {
            $order = StorefrontOrder::where('ref', $ref)->lockForUpdate()->first();
            if (!$order) {
                return null;
            }

            $order->payload = array_merge($order->payload, $data['patch']);
            $order->save();

            return $order->payload;
        });

        return $payload
            ? response()->json($payload)
            : response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
    }

    /**
     * Giành quyền gửi thư xác nhận cho một đơn.
     *
     * 200 = giành được, gửi đi. 409 = đã có người giành trước, đừng gửi. Hai
     * đường đều xác nhận được một đơn đã trả tiền (webhook PayOS và vòng poll),
     * nên thiếu chốt này là khách nhận hai, ba lá thư giống hệt nhau. Kiểm tra
     * và đánh dấu phải nằm trong cùng một transaction có khoá dòng, tách ra là
     * mở lại đúng khe hở đó.
     */
    public function claimEmail(string $ref)
    {
        $result = DB::transaction(function () use ($ref) {
            $order = StorefrontOrder::where('ref', $ref)->lockForUpdate()->first();
            if (!$order) {
                return ['status' => 404];
            }

            $payload = $order->payload;
            if (!empty($payload['confirmationEmailSentAt'])) {
                return ['status' => 409];
            }

            // Mili giây, cùng đơn vị với mọi mốc thời gian khác trong payload.
            $payload['confirmationEmailSentAt'] = now()->getTimestampMs();
            $order->payload = $payload;
            $order->save();

            return ['status' => 200, 'payload' => $payload];
        });

        if ($result['status'] === 404) {
            return response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
        }

        if ($result['status'] === 409) {
            return response()->json(['error' => 'Thư xác nhận đã được gửi.'], 409);
        }

        return response()->json($result['payload']);
    }

    /** Trả lại quyền khi gửi thư hỏng, để lần xác nhận sau còn thử lại được. */
    public function releaseEmail(string $ref)
    {
        $payload = DB::transaction(function () use ($ref) {
            $order = StorefrontOrder::where('ref', $ref)->lockForUpdate()->first();
            if (!$order) {
                return null;
            }

            $payload = $order->payload;
            unset($payload['confirmationEmailSentAt']);
            $order->payload = $payload;
            $order->save();

            return $payload;
        });

        return $payload
            ? response()->json($payload)
            : response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
    }
}
