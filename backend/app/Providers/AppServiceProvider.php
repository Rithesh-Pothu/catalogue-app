<?php

namespace App\Providers;

use App\Catalogue\Catalogue;
use App\Catalogue\FakeStoreProductSource;
use App\Catalogue\ProductSource;
use App\Enquiries\Enquiries;
use App\Enquiries\LaravelMailProvider;
use App\Enquiries\MailProvider;
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

        // The one place that decides how email is delivered (ADR 0002).
        $this->app->bind(MailProvider::class, LaravelMailProvider::class);

        $this->app->bind(Enquiries::class, fn ($app) => new Enquiries(
            $app->make(Catalogue::class),
            $app->make(MailProvider::class),
            config('catalogue.sales_inbox'),
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
