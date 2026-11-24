<?php

namespace Tests\Feature;

use App\Catalogue\InMemoryProductSource;
use App\Catalogue\Product;
use App\Catalogue\ProductSource;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_a_product_page_gets_the_product_with_its_rating(): void
    {
        config(['catalogue.featured_product_ids' => [1]]);
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(1, 'Backpack', 109.95, 'A sturdy backpack', "men's clothing", 'https://img.test/1.png', 3.9),
            new Product(3, 'Hard drive', 64.0, 'A portable drive', 'electronics', 'https://img.test/3.png', 3.3),
        ]));

        $response = $this->getJson('/api/products/3');

        $response->assertOk()->assertExactJson(['data' => [
            'id' => 3,
            'title' => 'Hard drive',
            'price' => 64.0,
            'description' => 'A portable drive',
            'category' => 'electronics',
            'image' => 'https://img.test/3.png',
            'rating' => 3.3,
        ]]);
    }

    public function test_a_featured_product_page_shows_no_rating(): void
    {
        config(['catalogue.featured_product_ids' => [1]]);
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(1, 'Backpack', 109.95, 'A sturdy backpack', "men's clothing", 'https://img.test/1.png', 3.9),
        ]));

        $response = $this->getJson('/api/products/1');

        $response->assertOk()->assertJsonPath('data.title', 'Backpack')->assertJsonPath('data.rating', null);
    }

    public function test_an_unknown_product_is_not_found(): void
    {
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(1, 'Backpack', 109.95, 'A sturdy backpack', "men's clothing", 'https://img.test/1.png', 3.9),
        ]));

        $response = $this->getJson('/api/products/99');

        $response->assertNotFound()->assertExactJson(['message' => 'Product not found.']);
    }

    public function test_a_product_id_that_is_not_a_number_is_not_found(): void
    {
        $this->app->instance(ProductSource::class, new InMemoryProductSource([]));

        $this->getJson('/api/products/backpack')->assertNotFound();
    }
}
