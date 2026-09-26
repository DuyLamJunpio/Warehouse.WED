<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Một thành phần vật lý trong một biến thể combo. */
class ProductComboItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_variant_id',
        'component_variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function comboVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'combo_variant_id');
    }

    public function componentVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'component_variant_id');
    }
}
