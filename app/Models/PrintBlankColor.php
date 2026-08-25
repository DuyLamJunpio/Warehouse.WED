<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một màu áo của phôi.
 *
 * `tone` là trường riêng chứ không suy từ mã màu mỗi lần đọc: xám mélange nằm
 * đúng giữa, và việc nó có cần lót trắng hay không là quyết định của người in
 * chứ không phải của một ngưỡng độ sáng. Suy tự động chỉ là giá trị gợi ý lúc
 * tạo, sau đó chủ shop sửa đè được.
 */
class PrintBlankColor extends Model
{
    public $timestamps = false;

    protected $fillable = ['print_blank_id', 'name', 'hex', 'tone', 'sort_order', 'is_active'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function blank(): BelongsTo
    {
        return $this->belongsTo(PrintBlank::class, 'print_blank_id');
    }

    /**
     * Tông gợi ý từ mã màu, theo độ sáng cảm nhận (ITU-R BT.601).
     *
     * Ngưỡng 0.62 chứ không phải 0.5: mực trắng vẫn cần lót trên cả những màu
     * trung bình như xám tiêu hay be đậm, nên nghiêng về phía "tối" an toàn hơn
     * — báo thiếu tiền lót thì shop chịu lỗ, báo thừa thì khách còn hỏi lại được.
     */
    public static function suggestTone(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return 'light';
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) < 0.62 ? 'dark' : 'light';
    }
}
