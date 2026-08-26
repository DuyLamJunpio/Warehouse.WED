<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image',
        'description',
        'sort_order',
        'status',
    ];

    // Nếu bạn sử dụng kiểu ngày tháng carbon khác với mặc định
    protected $dates = ['deleted_at'];

    public function products()
    {
        return $this->hasMany(Product::class, 'categories_id');
    }

    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Chỉ lấy danh mục gốc (không có cha).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
