<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLocation extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'zone', 'shelf', 'level', 'code'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function isShelfFull($zone, $shelf)
    {
        $count = self::where('zone', $zone)->where('shelf', $shelf)->count();
        return $count >= 10; // Giả sử mỗi giá có tối đa 10 tầng
    }

    public static function isPositionAvailable($zone, $shelf, $level)
    {
        return !self::where('zone', $zone)->where('shelf', $shelf)->where('level', $level)->exists();
    }
    protected static function boot()
    {
        parent::boot();

        // Tự động tạo mã khi tạo vị trí mới
        static::creating(function ($location) {
            $location->code = 'K' . $location->zone . '-' . $location->shelf . '-' . $location->level;
        });
    }
}
