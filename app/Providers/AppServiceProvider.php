<?php

namespace App\Providers;

use App\Models\Categories;
use App\Models\Invoice;
use App\Models\ImageModel;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Observers\CatalogueObserver;
use App\Observers\OrderStatusObserver;
use App\Services\OrderStatusMailer;
use App\Services\StorefrontNotifier;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Singleton để cả request chỉ gộp thành một lần báo cho web bán hàng.
        $this->app->singleton(StorefrontNotifier::class);

        // Cũng singleton: một đơn đổi trạng thái hai lần trong cùng một request
        // thì vẫn chỉ nhận một lá thư, và việc gom đó nằm trong chính đối tượng.
        $this->app->singleton(OrderStatusMailer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // URL::forceScheme('https');

        // Đổi bất cứ thứ gì web bán hàng đang hiển thị thì báo nó làm mới.
        foreach ([Product::class, ProductVariant::class, ImageModel::class, Categories::class] as $model) {
            $model::observe(CatalogueObserver::class);
        }

        // Đơn hàng đổi trạng thái thì khách được báo bằng thư. Đặt ở observer vì
        // trạng thái bị đổi ở nhiều đường, không riêng nút bấm của nhân viên.
        Invoice::observe(OrderStatusObserver::class);
    }
}
