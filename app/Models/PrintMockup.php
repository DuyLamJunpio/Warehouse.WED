<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Ảnh áo trải phẳng, một tấm cho mỗi màu x mỗi góc nhìn.
 *
 * Mockup KHÁC ảnh bán hàng. Ảnh bán hàng là người mẫu mặc, chụp nghiêng; overlay
 * khung in lên đó là lệch. Mockup phải là áo trải phẳng, chính diện, và CÙNG một
 * khung cắt cho mọi màu — vùng in khai một lần cho cả phôi, nên tấm nào cắt cúp
 * khác là khung in sai trên đúng tấm đó mà không ai phát hiện cho tới lúc in hỏng.
 *
 * `offset_x/y` là đường thoát cho tấm không chụp lại được: chỉnh lệch riêng cho
 * một màu thay vì bắt khai lại toàn bộ vùng in.
 */
class PrintMockup extends Model
{
    protected $fillable = [
        'print_blank_id', 'print_blank_color_id', 'view', 'path',
        'width_px', 'height_px', 'offset_x', 'offset_y',
    ];

    protected $casts = [
        'width_px' => 'integer',
        'height_px' => 'integer',
        'offset_x' => 'float',
        'offset_y' => 'float',
    ];

    /** Lệch tỉ lệ khung so với một tấm chuẩn, tính theo phần trăm. */
    public const MAX_ASPECT_DRIFT = 2.0;

    public function blank(): BelongsTo
    {
        return $this->belongsTo(PrintBlank::class, 'print_blank_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(PrintBlankColor::class, 'print_blank_color_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk(config('filesystems.default'))->url($this->path) : null;
    }

    /**
     * Tấm mới có cùng khung với tấm chuẩn không.
     *
     * So tỉ lệ chứ không so số pixel: ảnh 2000x2300 và 1000x1150 là cùng khung,
     * chỉ khác độ phân giải, và khung in vẽ theo phần trăm nên vẫn đúng chỗ.
     */
    public static function aspectDrift(int $w, int $h, int $refW, int $refH): float
    {
        if ($h <= 0 || $refH <= 0) {
            return 0.0;
        }

        $ratio = $w / $h;
        $refRatio = $refW / $refH;

        return abs($ratio - $refRatio) / $refRatio * 100;
    }
}
