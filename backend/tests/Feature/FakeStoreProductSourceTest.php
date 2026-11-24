<?php

namespace Tests\Feature;

use App\Catalogue\Product;
use App\Catalogue\ProductSource;
use App\Catalogue\ProductSourceUnavailable;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FakeStoreProductSourceTest extends TestCase
{
    public function test_a_recorded_fake_store_response_becomes_products_in_our_own_shape(): void
    {
        // tests/Fixtures/fakestore-products.json is a slice of a real response
        // from https://fakestoreapi.com/products (products 1, 5 and 9).
        Http::fake([
            'fakestoreapi.com/products' => Http::response(
                file_get_contents(base_path('tests/Fixtures/fakestore-products.json')),
                200,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $products = $this->app->make(ProductSource::class)->all();

        $this->assertCount(3, $products);
        $this->assertEquals(
            new Product(
                1,
                'Fjallraven - Foldsack No. 1 Backpack, Fits 15 Laptops',
                109.95,
                'Your perfect pack for everyday use and walks in the forest. Stash your laptop (up to 15 inches) in the padded sleeve, your everyday',
                "men's clothing",
                'https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_t.png',
                3.9,
            ),
            $products[0],
        );
        $this->assertSame([1, 5, 9], array_map(fn (Product $product) => $product->id, $products));
    }

    public function test_a_failing_fake_store_means_the_source_is_unavailable(): void
    {
        Http::fake(['fakestoreapi.com/products' => Http::response('Bad gateway', 502)]);

        $this->expectException(ProductSourceUnavailable::class);

        $this->app->make(ProductSource::class)->all();
    }
}
