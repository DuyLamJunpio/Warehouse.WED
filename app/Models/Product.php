<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'categories_id',
        'product_name',
        'barcode',
        'unit',
        'import_price',
        'sell_price',
        'status',
    ];

    protected $dates = ['deleted_at'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'categories_id')->withTrashed();
    }

    public function productInvoices()
    {
        return $this->hasMany(ProductInvoice::class);
    }

    public function productImage()
    {
        return $this->hasMany(ImageModel::class, 'product_id', 'id');
    }

    public function imageModel()
    {
        return $this->hasMany(ImageModel::class);
    }
    public function location()
    {
        return $this->hasOne(ProductLocation::class);
    }
    public function expiries()
    {
        return $this->hasMany(Expiry::class, 'product_id', 'id');
    }
}
