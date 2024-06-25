<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInvoice extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'product_id', 'quantity'];
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class)->withTrashed();
    }
}
