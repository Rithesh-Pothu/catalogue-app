<?php

namespace Tests\Feature;

use App\Catalogue\Product;
use App\Catalogue\ProductSource;
use App\Catalogue\ProductSourceUnavailable;
use Tests\TestCase;

class CatalogueCacheTest extends TestCase
{
    private object $source;

    protected function setUp(): void
    {
        parent::setUp();

        // Real cache stores (file, redis, database) serialize what they keep,
        // and refuse to turn it back into objects. The default test store does
        // not serialize, which would hide that, so these tests make it behave
        // like the real ones.
        config(['cache.stores.array.serialize' => true, 'cache.serializable_classes' => false]);
        $this->app->make('cache')->forgetDriver('array');

        // A source that counts how often it is read, and can be told to fail.
        $this->source = new class implements ProductSource
        {
            public int $reads = 0;

            public bool $failing = false;

            public function all(): array
            {
                $this->reads++;
                if ($this->failing) {
                    throw new ProductSourceUnavailable('down');
                }

                return [
                    new Product(1, 'Backpack', 109.95, 'A backpack', "men's clothing", 'https://img.test/1.png', 3.9),
                    new Product(2, 'Bracelet', 695.0, 'A bracelet', 'jewelery', 'https://img.test/2.png', 4.6),
                ];
            }
        };
        $this->app->instance(ProductSource::class, $this->source);
        config(['catalogue.featured_product_ids' => [2]]);
    }

    private function viewEverything(): void
    {
        $this->getJson('/api/featured-products')->assertOk();
        $this->getJson('/api/categories')->assertOk();
        $this->getJson("/api/categories/jewelery/products")->assertOk();
        $this->getJson('/api/search?q=pack')->assertOk();
        $this->getJson('/api/products/1')->assertOk();
    }

    public function test_repeated_views_within_five_minutes_read_the_source_once(): void
    {
        $this->viewEverything();
        $this->travel(4)->minutes();
        $this->travel(59)->seconds();
        $this->viewEverything();

        $this->assertSame(1, $this->source->reads);
    }

    public function test_after_five_minutes_the_source_is_read_again(): void
    {
        $this->viewEverything();
        $this->travel(5)->minutes();
        $this->travel(1)->seconds();
        $this->viewEverything();

        $this->assertSame(2, $this->source->reads);
    }

    public function test_a_failed_read_is_not_cached_so_the_next_view_tries_again(): void
    {
        $this->source->failing = true;
        $this->getJson('/api/featured-products')->assertStatus(503);
        $this->getJson('/api/featured-products')->assertStatus(503);
        $this->assertSame(2, $this->source->reads);

        $this->source->failing = false;
        $this->getJson('/api/featured-products')->assertOk();
        $this->assertSame(3, $this->source->reads);
    }

    public function test_caching_does_not_change_what_visitors_see(): void
    {
        $first = $this->getJson('/api/featured-products')->assertOk()->json();
        $second = $this->getJson('/api/featured-products')->assertOk()->json();

        $this->assertSame($first, $second);
        $this->assertSame([2], array_column($second['data'], 'id'));
        $this->assertNull($second['data'][0]['rating']);
        $this->assertSame(1, $this->source->reads);
    }

    public function test_changing_the_featured_list_takes_effect_without_waiting_for_the_cache(): void
    {
        $this->getJson('/api/featured-products')->assertOk();

        config(['catalogue.featured_product_ids' => [1]]);

        $ids = array_column($this->getJson('/api/featured-products')->assertOk()->json('data'), 'id');
        $this->assertSame([1], $ids);
        $this->assertSame(1, $this->source->reads);
    }
}
