<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
