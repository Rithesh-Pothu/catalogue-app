<?php

namespace App\Providers;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
