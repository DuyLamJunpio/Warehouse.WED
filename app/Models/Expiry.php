<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expiry extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'expiry_date',
        'quantity_exp',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
