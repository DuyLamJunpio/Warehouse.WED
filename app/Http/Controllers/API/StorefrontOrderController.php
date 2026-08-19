<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StorefrontOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $order = StorefrontOrder::updateOrCreate(
            ['ref' => $data['ref']],
            ['order_code' => $data['order_code'], 'payload' => $data['payload']],
        );

        StorefrontOrder::prune();

        return response()->json($order->payload, 201);
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
