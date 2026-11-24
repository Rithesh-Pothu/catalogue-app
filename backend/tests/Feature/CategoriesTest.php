<?php

namespace Tests\Feature;

use App\Catalogue\InMemoryProductSource;
use App\Catalogue\Product;
use App\Catalogue\ProductSource;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    public function test_categories_are_listed_once_each_in_the_order_the_source_returns_them(): void
    {
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(1, 'Backpack', 109.95, 'A backpack', "men's clothing", 'https://img.test/1.png', 3.9),
            new Product(2, 'Bracelet', 695.0, 'A bracelet', 'jewelery', 'https://img.test/2.png', 4.6),
            new Product(3, 'T-shirt', 22.3, 'A t-shirt', "men's clothing", 'https://img.test/3.png', 4.1),
            new Product(4, 'Hard drive', 64.0, 'A drive', 'electronics', 'https://img.test/4.png', 3.3),
        ]));

        $response = $this->getJson('/api/categories');

        $response->assertOk()->assertExactJson(['data' => ["men's clothing", 'jewelery', 'electronics']]);
    }
}
