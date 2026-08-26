<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Một hình có thể đặt lên áo: sticker shop cung cấp sẵn, hoặc file khách tải lên.
 *
 * Chung một bảng vì cả hai được đặt lên áo y hệt nhau — khác nhau chỉ ở chỗ ai
 * đưa vào và có tính phí hay không. Nhờ vậy luồng "tải template về tự thiết kế
 * rồi tải lên" không cần cấu trúc riêng: nó chỉ là một asset kind=upload phủ
 * trọn vùng in.
 */
class PrintAsset extends Model
{
    public const KIND_LIBRARY = 'library';
    public const KIND_UPLOAD = 'upload';

    protected $fillable = [
        'kind', 'name', 'tag', 'path', 'width_px', 'height_px', 'mime', 'bytes',
        'has_alpha', 'fee', 'allowed_technique_ids', 'min_width_mm', 'max_width_mm',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'width_px' => 'integer',
        'height_px' => 'integer',
        'bytes' => 'integer',
        'has_alpha' => 'boolean',
        'fee' => 'integer',
        'allowed_technique_ids' => 'array',
        'min_width_mm' => 'integer',
        'max_width_mm' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk(config('filesystems.default'))->url($this->path) : null;
    }

    /** null trong allowed_technique_ids nghĩa là dùng được với mọi kỹ thuật. */
    public function allowsTechnique(int $techniqueId): bool
    {
        $allowed = $this->allowed_technique_ids;

        return empty($allowed) || in_array($techniqueId, $allowed);
    }

    /**
     * Độ phân giải thật khi hình được in ở bề rộng này.
     *
     * Đây là con số quyết định hình in ra có rỗ hay không, và nó phải tính ở
     * kích thước THẬT trên vải chứ không phải kích thước trên màn hình.
     */
    public function dpiAt(float $widthMm): float
    {
        if ($widthMm <= 0) {
            return 0;
        }

        return $this->width_px / ($widthMm / 25.4);
    }

    public function toStorefrontArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tag' => $this->tag,
            'url' => $this->url,
            'width_px' => $this->width_px,
            'height_px' => $this->height_px,
            'has_alpha' => $this->has_alpha,
            'fee' => $this->fee,
            'allowed_technique_ids' => $this->allowed_technique_ids,
            'min_width_mm' => $this->min_width_mm,
            'max_width_mm' => $this->max_width_mm,
        ];
    }
}
