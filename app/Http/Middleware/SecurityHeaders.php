<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header bảo mật cho mọi response.
 *
 * Quan trọng nhất là X-Frame-Options: trước đây trang quản trị có thể bị nhúng
 * trong iframe của site khác (clickjacking) — kẻ tấn công phủ lớp trong suốt lên
 * nút "Xóa sản phẩm" rồi dụ nhân viên đang đăng nhập bấm vào.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // CSP nới đúng những nguồn trang quản trị đang thật sự dùng:
        //   script  : jQuery + Swiper qua CDN
        //   style   : Tailwind sinh class nội tuyến nên cần 'unsafe-inline'
        //   img     : data: cho icon SVG nhúng, blob: cho ảnh/video xem trước
        //   font    : Google Fonts
        // Chưa siết script-src về 'self' được vì các view còn nhiều <script> nội
        // tuyến; siết được sau khi gom hết JS vào bundle Vite.
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: blob: https:",
            "media-src 'self' data: blob:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
