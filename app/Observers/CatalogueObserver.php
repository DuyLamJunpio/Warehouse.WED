<?php

namespace App\Observers;

use App\Services\StorefrontNotifier;
use Illuminate\Database\Eloquent\Model;

/**
 * Bất cứ thứ gì web bán hàng đang hiển thị mà đổi thì báo cho nó biết.
 *
 * Gắn vào model chứ không gắn vào controller: sản phẩm bị sửa từ nhiều đường —
 * trang quản trị, API, bán tại quầy, đơn từ web bán hàng trừ kho — và mỗi lần
 * thêm một đường mới lại nhớ gọi thông báo là kiểu gì cũng có chỗ quên.
 *
 * Lưu ý: cập nhật hàng loạt (`Model::where(...)->update(...)`) không bắn sự
 * kiện Eloquent nên không kích hoạt được ở đây. Chấp nhận được: những chỗ đó
 * luôn đi kèm một lượt lưu sản phẩm trong cùng request.
 */
class CatalogueObserver
{
    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function saved(Model $model): void
    {
        $this->notifier->markDirty();
    }

    public function deleted(Model $model): void
    {
        $this->notifier->markDirty();
    }

    public function restored(Model $model): void
    {
        $this->notifier->markDirty();
    }

    public function forceDeleted(Model $model): void
    {
        $this->notifier->markDirty();
    }
}
