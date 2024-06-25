<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'status'];

    // Nếu bạn sử dụng kiểu ngày tháng carbon khác với mặc định
    protected $dates = ['deleted_at'];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
