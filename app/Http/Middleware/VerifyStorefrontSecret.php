<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chỉ cho web bán hàng gọi các endpoint đổi trạng thái tiền bạc.
 *
 * PayOS gửi webhook tới web bán hàng, nơi đó đã kiểm tra chữ ký HMAC của PayOS
 * rồi mới báo sang đây. Nhưng bản thân endpoint bên này trước đó không xác thực
 * gì cả: chỉ cần biết mã đơn là đánh dấu được "đã thanh toán" — mà chính khách
 * đặt hàng là người biết mã đơn của mình.
 *
 * Đây là chặng server-to-server nên bí mật dùng chung là đủ; không dùng session
 * hay Sanctum vì phía gọi là máy chủ Next.js, không phải trình duyệt.
 */
class VerifyStorefrontSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.storefront.secret');

        // Chưa cấu hình thì chặn hết, không im lặng cho qua: thà đơn không được
        // tự động xác nhận (nhân viên vẫn đối chiếu tay) còn hơn để ngỏ.
        if ($expected === '') {
            Log::critical('STOREFRONT_WEBHOOK_SECRET chưa được cấu hình, đã từ chối yêu cầu.');

            return response()->json(['error' => 'Máy chủ chưa cấu hình xác thực.'], 503);
        }

        $provided = (string) $request->header('X-Storefront-Secret', '');

        // hash_equals so sánh trong thời gian hằng định, không rò rỉ qua timing.
        if ($provided === '' || !hash_equals($expected, $provided)) {
            Log::warning('Từ chối yêu cầu thiếu hoặc sai bí mật web bán hàng.', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json(['error' => 'Không có quyền.'], 401);
        }

        return $next($request);
    }
}
