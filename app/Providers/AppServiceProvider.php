<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Promotion;
use App\Models\Unit;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductRecipeObserver;
use App\Observers\PromotionObserver;
use App\Observers\UnitObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        Unit::observe(UnitObserver::class);
        Promotion::observe(PromotionObserver::class);
        ProductRecipe::observe(ProductRecipeObserver::class);
    }
}
