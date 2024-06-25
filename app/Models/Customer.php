<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'avatar',
        'customer_name',
        'customer_phone',
        'customer_email',
        'address',
        'status',
    ];
    protected $appends = ['total_invoices'];

    protected $dates = ['deleted_at'];
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Getter cho tổng số hóa đơn
    public function getTotalInvoicesAttribute()
    {
        return $this->invoices()->count();
    }
    public function invoicesPaid()
    {
        return $this->hasMany(Invoice::class)->where('pay_status', 1);
    }

    /**
     * Mối quan hệ với hóa đơn còn nợ.
     */
    public function invoicesOwed()
    {
        return $this->hasMany(Invoice::class)->where('pay_status', 0);
    }
}
