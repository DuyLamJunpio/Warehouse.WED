<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Một bậc khổ in.
 *
 * Khung bao của khách được xếp vào bậc nhỏ nhất chứa được. Bậc chứ không phải
 * cm² vì xưởng in tính tiền theo tờ decal / khung lụa: ba sticker nhỏ nằm gọn
 * trong A5 là tiền A5, không phải ba lần tiền.
 */
class PrintSizeTier extends Model
{
    protected $fillable = ['name', 'width_mm', 'height_mm', 'sort_order', 'is_active'];

    protected $casts = [
        'width_mm' => 'integer',
        'height_mm' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function toPricingArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'width_mm' => $this->width_mm,
            'height_mm' => $this->height_mm,
        ];
    }
}
