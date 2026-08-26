<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Báo cho web bán hàng biết catalogue vừa đổi.
 *
 * Web bán hàng đọc thẳng API của trang này và tự làm mới mỗi phút. Cú gọi này
 * rút quãng chờ đó về gần như tức thì: sửa sản phẩm xong, bấm Lưu, khách tải
 * lại trang là thấy.
 *
 * Hai điều cố ý:
 *
 *   1. Gộp một lần cho mỗi request. Lưu một sản phẩm có thể chạm vào chục dòng
 *      ảnh và biến thể; gọi mỗi dòng một lần thì web bị dội hàng chục cú.
 *   2. Gửi sau khi đã trả trang cho nhân viên (`terminating`). Web bán hàng nằm
 *      ở máy khác, không có lý do gì để nhân viên phải chờ chặng mạng đó.
 *
 * Thất bại thì chỉ ghi log: web vẫn tự làm mới theo chu kỳ, còn thao tác lưu
 * bên này thì đã xong và không được phép hỏng vì web bán hàng đang tắt.
 */
class StorefrontNotifier
{
    private bool $dirty = false;

    private bool $scheduled = false;

    /** Đánh dấu có thay đổi; cú gọi thật diễn ra một lần vào cuối request. */
    public function markDirty(): void
    {
        $this->dirty = true;

        if ($this->scheduled) {
            return;
        }

        $this->scheduled = true;
        app()->terminating(fn () => $this->flush());
    }

    public function flush(): void
    {
        if (! $this->dirty) {
            return;
        }

        $this->dirty = false;

        $url = rtrim((string) config('services.storefront.url'), '/');
        $secret = (string) config('services.storefront.secret');

        // Chưa cấu hình thì im lặng bỏ qua: web bán hàng vẫn tự làm mới theo
        // chu kỳ, và không phải bản cài nào cũng có web bán hàng đi kèm.
        if ($url === '' || $secret === '') {
            return;
        }

        try {
            $response = Http::withHeaders(['X-Warehouse-Secret' => $secret])
                ->timeout(3)
                ->post($url.'/api/revalidate');

            if ($response->failed()) {
                Log::warning('Web bán hàng từ chối yêu cầu làm mới catalogue.', [
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Không báo được cho web bán hàng làm mới catalogue.', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
