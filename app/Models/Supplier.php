<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'supplier_name',
        'supplier_phone',
        'address',
        'status',
        'tax',
    ];

    protected $dates = ['deleted_at'];
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function total_amount()
    {
        return $this->hasMany(Invoice::class)->where('pay_status', 0);
    }
}
