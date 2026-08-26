<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Một kỹ thuật in do chủ shop tự tạo.
 *
 * Ràng buộc của kỹ thuật nằm ngay trong bản ghi chứ không nằm trong mã: thêu
 * không in được ảnh chụp, in lụa giới hạn số màu. Studio bên web đọc đúng mấy
 * trường này để chặn khách, nên tạo thêm "In chuyển nhiệt 3D" là nó tự biết
 * phải chặn gì mà không ai phải sửa code.
 */
class PrintTechnique extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'max_colors', 'accepts_photo', 'accepts_gradient',
        'needs_underbase', 'min_dpi', 'file_types', 'lead_days', 'moq', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'max_colors' => 'integer',
        'accepts_photo' => 'boolean',
        'accepts_gradient' => 'boolean',
        'needs_underbase' => 'boolean',
        'min_dpi' => 'integer',
        'lead_days' => 'integer',
        'moq' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function blanks(): BelongsToMany
    {
        return $this->belongsToMany(PrintBlank::class, 'print_blank_technique');
    }

    /** Đuôi tệp nhận, đã tách thành mảng chữ thường. */
    public function fileTypeList(): array
    {
        return collect(explode(',', (string) $this->file_types))
            ->map(fn ($t) => strtolower(trim($t)))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Hình dạng đi vào ảnh chụp bảng giá.
     *
     * Chụp cả ràng buộc chứ không chỉ tên: đơn cũ phải tính lại được y hệt kể
     * cả khi sau đó chủ shop nới số màu tối đa của kỹ thuật này.
     */
    public function toPricingArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'max_colors' => $this->max_colors,
            'accepts_photo' => $this->accepts_photo,
            'accepts_gradient' => $this->accepts_gradient,
            'needs_underbase' => $this->needs_underbase,
            'min_dpi' => $this->min_dpi,
            'file_types' => $this->fileTypeList(),
            'lead_days' => $this->lead_days,
            'moq' => $this->moq,
            'is_active' => $this->is_active,
        ];
    }
}
