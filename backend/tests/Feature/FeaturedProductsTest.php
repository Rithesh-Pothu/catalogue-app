<?php

namespace Tests\Feature;

use App\Catalogue\InMemoryProductSource;
use App\Catalogue\Product;
use App\Catalogue\ProductSource;
use App\Catalogue\ProductSourceUnavailable;
use Tests\TestCase;

class FeaturedProductsTest extends TestCase
{
    public function test_the_home_page_gets_the_configured_featured_products_in_our_own_shape(): void
    {
        config(['catalogue.featured_product_ids' => [2, 1]]);
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(1, 'Backpack', 109.95, 'A sturdy backpack', "men's clothing", 'https://img.test/1.png', 3.9),
            new Product(2, 'Bracelet', 695.0, 'A gold bracelet', 'jewelery', 'https://img.test/2.png', 4.6),
            new Product(3, 'Hard drive', 64.0, 'Not featured', 'electronics', 'https://img.test/3.png', 3.3),
        ]));

        $response = $this->getJson('/api/featured-products');

        $response->assertOk()->assertExactJson(['data' => [
            [
                'id' => 2,
                'title' => 'Bracelet',
                'price' => 695.0,
                'description' => 'A gold bracelet',
                'category' => 'jewelery',
                'image' => 'https://img.test/2.png',
                'rating' => null,
            ],
            [
                'id' => 1,
                'title' => 'Backpack',
                'price' => 109.95,
                'description' => 'A sturdy backpack',
                'category' => "men's clothing",
                'image' => 'https://img.test/1.png',
                'rating' => null,
            ],
        ]]);
    }

    public function test_a_featured_product_missing_from_the_source_is_skipped(): void
    {
        config(['catalogue.featured_product_ids' => [1, 99, 2]]);
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(1, 'Backpack', 109.95, 'A sturdy backpack', "men's clothing", 'https://img.test/1.png', 3.9),
            new Product(2, 'Bracelet', 695.0, 'A gold bracelet', 'jewelery', 'https://img.test/2.png', 4.6),
        ]));

        $response = $this->getJson('/api/featured-products');

        $response->assertOk()->assertJsonCount(2, 'data');
        $this->assertSame([1, 2], array_column($response->json('data'), 'id'));
    }

    public function test_featured_products_answer_503_unavailable_when_the_source_is_down(): void
    {
        $this->app->instance(ProductSource::class, new class implements ProductSource
        {
            public function all(): array
            {
                throw new ProductSourceUnavailable('The source is down.');
            }
        });

        $response = $this->getJson('/api/featured-products');

        $response->assertStatus(503)->assertExactJson(['message' => 'Products are unavailable right now.']);
    }
}
