<?php

namespace App\Providers;

use App\Models\Categories;
use App\Models\ImageModel;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Observers\CatalogueObserver;
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
    }
}
