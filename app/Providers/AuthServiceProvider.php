<?php

namespace App\Providers;

//use Illuminate\Support\Facades\Gate;

use App\Models\Categories;
use App\Models\Product;
use App\Models\Supplier;
use App\Policies\CategoriesPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SupplierPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Supplier::class => SupplierPolicy::class,
        Categories::class => CategoriesPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {

    }
}
