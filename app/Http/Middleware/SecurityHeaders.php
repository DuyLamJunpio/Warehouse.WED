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
        //   media   : kho ảnh/video ngoài (Supabase Storage) — video sản phẩm và
        //             banner nằm ở đó, không khai thì trình duyệt chặn im lặng
        //   font    : Google Fonts
        // Chưa siết script-src về 'self' được vì các view còn nhiều <script> nội
        // tuyến; siết được sau khi gom hết JS vào bundle Vite.
        // Suy từ cấu hình disk thay vì gõ cứng tên miền: đổi bucket hay đổi nhà
        // cung cấp kho ảnh là chỉ sửa .env, CSP tự khớp theo.
        $mediaHost = $this->mediaOrigin();

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: blob: https:",
            trim("media-src 'self' data: blob: {$mediaHost}"),
            "font-src 'self' data: https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    /**
     * Gốc (scheme + host) của kho ảnh ngoài, rỗng nếu đang dùng disk local.
     */
    private function mediaOrigin(): string
    {
        $url = config('filesystems.disks.'.config('filesystems.default').'.url');

        if (! is_string($url) || $url === '') {
            return '';
        }

        $parts = parse_url($url);

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        return $parts['scheme'].'://'.$parts['host'];
    }
}
