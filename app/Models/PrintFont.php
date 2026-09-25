<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Một phông chữ shop in được.
 *
 * Danh sách này là ranh giới giữa "khách muốn gì" và "xưởng làm được gì". Cho
 * khách gõ tên phông tự do là nhận về những đơn không sản xuất nổi; giới hạn
 * trong bảng này thì mỗi lựa chọn của khách đều có một tệp phông thật nằm trong
 * máy của xưởng.
 */
class PrintFont extends Model
{
    protected $fillable = ['name', 'family', 'file_path', 'sort_order', 'is_active'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    /**
     * `@font-face` cho tên CSS "print-font-{id}" mà hệ thống đặt lúc tải tệp lên.
     *
     * Studio tự khai báo phông trong trang của nó. Ra khỏi studio — thư viện,
     * trang duyệt mẫu, file SVG — cái tên ấy không trỏ tới đâu, và chữ lặng lẽ
     * rơi về phông mặc định. Phông hệ thống không có tệp nên không cần khai báo.
     */
    public function fontFace(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        // Link nằm trong url("…"): không để nó thoát khỏi chuỗi hay thẻ <style>.
        $url = str_replace(['\\', '"', '<', '>', "\n", "\r"], ['%5C', '%22', '%3C', '%3E', '', ''], (string) $this->url);

        return sprintf('@font-face{font-family:"print-font-%d";src:url("%s");}', $this->id, $url);
    }

    /** @param  iterable<PrintFont>  $fonts */
    public static function fontFaceCss(iterable $fonts): string
    {
        return collect($fonts)->map(fn (PrintFont $font) => $font->fontFace())->filter()->implode('');
    }

    /** Tên tệp khi tải về: tên phông dễ đọc thay cho chuỗi uuid trong kho lưu trữ. */
    public function downloadName(): string
    {
        $extension = pathinfo((string) $this->file_path, PATHINFO_EXTENSION);

        return (Str::slug($this->name) ?: 'phong-' . $this->id) . ($extension !== '' ? '.' . $extension : '');
    }

    public function toStorefrontArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Studio dùng nguyên chuỗi này làm `font-family`, nên nó phải là một
            // ngăn xếp hợp lệ kể cả khi tệp woff2 chưa tải xong.
            'family' => $this->family,
            'url' => $this->url,
        ];
    }
}
