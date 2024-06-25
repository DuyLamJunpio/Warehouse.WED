<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'total_amount',
        'invoice_type',
        'customer_id',
        'supplier_id',
        'pay_status',
        'due_date',
        'note',
        'term',
        'discount',
        'signature_name',
        'signature',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function expiries()
    {
        return $this->hasManyThrough(Expiry::class, ProductInvoice::class, 'invoice_id', 'product_id', 'id', 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }
    public function productInvoices()
    {
        return $this->hasMany(ProductInvoice::class);
    }

    public static function searchByInvoiceId($invoiceId)
    {
        return self::where('id', $invoiceId)
            ->with([
                'user' => function ($query) {
                    $query->withTrashed();
                },
                'customer' => function ($query) {
                    $query->withTrashed();
                },
                'supplier' => function ($query) {
                    $query->withTrashed();
                },
                'productInvoices' => function ($query) {
                    $query->with(['product' => function ($query) {
                        $query->withTrashed()->with('productImage');
                    }]);
                }
            ])
            ->withTrashed()
            ->first();
    }

    public static function filterByTypeAndStatus($invoiceType = null, $payStatus = null)
    {
        $query = self::query();

        if (!is_null($invoiceType)) {
            $query->where('invoice_type', $invoiceType);
        }

        if (!is_null($payStatus)) {
            $query->where('pay_status', $payStatus);
        }

        return $query->get();
    }
}
