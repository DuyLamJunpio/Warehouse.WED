<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một vùng được phép in trên phôi: ngực, lưng, tay áo.
 *
 * Giữ hai hệ toạ độ cạnh nhau và không được lẫn:
 *
 *   width_mm / height_mm  kích thước thật trên vải — THỢ IN ĐỌC CÁI NÀY
 *   box_x/y/w/h           phần trăm trên ảnh mockup, chỉ để vẽ khung cho người xem
 *
 * Lưu theo pixel màn hình là đổi ảnh mockup một lần thì mọi đơn cũ in lệch.
 */
class PrintZone extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'print_blank_id', 'key', 'label', 'width_mm', 'height_mm',
        'box_x', 'box_y', 'box_w', 'box_h', 'max_placements', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'width_mm' => 'integer',
        'height_mm' => 'integer',
        'box_x' => 'float',
        'box_y' => 'float',
        'box_w' => 'float',
        'box_h' => 'float',
        'max_placements' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function blank(): BelongsTo
    {
        return $this->belongsTo(PrintBlank::class, 'print_blank_id');
    }

    public function toStorefrontArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'width_mm' => $this->width_mm,
            'height_mm' => $this->height_mm,
            'box' => [
                'x' => $this->box_x,
                'y' => $this->box_y,
                'w' => $this->box_w,
                'h' => $this->box_h,
            ],
            'max_placements' => $this->max_placements,
        ];
    }
}
