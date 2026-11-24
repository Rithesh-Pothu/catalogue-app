<?php

namespace App\Providers;

use App\Catalogue\Catalogue;
use App\Catalogue\FakeStoreProductSource;
use App\Catalogue\ProductSource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The one place that decides where products come from (ADR 0001).
        $this->app->bind(ProductSource::class, fn () => new FakeStoreProductSource(
            config('catalogue.fake_store.url'),
            config('catalogue.fake_store.timeout'),
        ));

        $this->app->bind(Catalogue::class, fn ($app) => new Catalogue(
            $app->make(ProductSource::class),
            config('catalogue.featured_product_ids'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
