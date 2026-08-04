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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

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

        // Behind a TLS-terminating reverse proxy (Railway, etc.), trustProxies alone
        // can still miss the forwarded-proto header depending on the platform - force
        // https explicitly whenever APP_URL says the app is served over https, so
        // generated asset()/route() URLs never come back as http:// (mixed content).
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
