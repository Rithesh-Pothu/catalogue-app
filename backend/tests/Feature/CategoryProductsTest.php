<?php

namespace Tests\Feature;

use App\Catalogue\InMemoryProductSource;
use App\Catalogue\Product;
use App\Catalogue\ProductSource;
use App\Catalogue\ProductSourceUnavailable;
use Tests\TestCase;

class CategoryProductsTest extends TestCase
{
    private function product(int $id, string $category, ?float $rating = 4.0): Product
    {
        return new Product($id, "Product {$id}", 10.0 + $id, "About {$id}", $category, "https://img.test/{$id}.png", $rating);
    }

    /**
     * @param  list<Product>  $products
     * @param  list<int>  $featured
     */
    private function catalogueOf(array $products, array $featured): void
    {
        config(['catalogue.featured_product_ids' => $featured]);
        $this->app->instance(ProductSource::class, new InMemoryProductSource($products));
    }

    /**
     * @return list<int>
     */
    private function idsIn(string $category): array
    {
        $response = $this->getJson('/api/categories/'.rawurlencode($category).'/products')->assertOk();

        return array_column($response->json('data'), 'id');
    }

    public function test_featured_products_come_first_then_the_rest_in_source_order(): void
    {
        $this->catalogueOf([
            $this->product(1, 'electronics'),
            $this->product(2, 'jewelery'),
            $this->product(3, 'electronics'),
            $this->product(4, 'electronics'),
            $this->product(5, 'electronics'),
        ], featured: [5, 3]);

        // Featured in the configured order (5, 3), then 1 and 4 as the source returned them.
        $this->assertSame([5, 3, 1, 4], $this->idsIn('electronics'));
    }

    public function test_a_category_with_no_featured_products_shows_its_products_in_source_order(): void
    {
        $this->catalogueOf([
            $this->product(1, 'electronics'),
            $this->product(2, 'jewelery'),
            $this->product(3, 'jewelery'),
        ], featured: [1]);

        $this->assertSame([2, 3], $this->idsIn('jewelery'));
    }

    public function test_a_featured_product_in_another_category_is_not_pulled_in(): void
    {
        $this->catalogueOf([
            $this->product(1, 'electronics'),
            $this->product(2, 'jewelery'),
        ], featured: [1]);

        $this->assertSame([2], $this->idsIn('jewelery'));
    }

    public function test_featured_products_show_no_rating_and_the_others_keep_theirs(): void
    {
        $this->catalogueOf([
            $this->product(1, 'electronics', 3.3),
            $this->product(2, 'electronics', 4.5),
        ], featured: [1]);

        $products = $this->getJson('/api/categories/electronics/products')->assertOk()->json('data');

        $this->assertNull($products[0]['rating']);
        $this->assertSame(4.5, $products[1]['rating']);
    }

    public function test_a_category_name_with_spaces_and_an_apostrophe_is_found(): void
    {
        $this->catalogueOf([$this->product(1, "men's clothing")], featured: []);

        $this->assertSame([1], $this->idsIn("men's clothing"));
    }

    public function test_an_unknown_category_is_not_found(): void
    {
        $this->catalogueOf([$this->product(1, 'electronics')], featured: []);

        $this->getJson('/api/categories/toys/products')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Category not found.']);
    }

    public function test_category_names_are_matched_exactly(): void
    {
        $this->catalogueOf([$this->product(1, 'electronics')], featured: []);

        $this->getJson('/api/categories/Electronics/products')->assertNotFound();
    }

    public function test_an_unavailable_product_source_answers_503(): void
    {
        $this->app->instance(ProductSource::class, new class implements ProductSource
        {
            public function all(): array
            {
                throw new ProductSourceUnavailable('down');
            }
        });

        $this->getJson('/api/categories/electronics/products')
            ->assertStatus(503)
            ->assertExactJson(['message' => 'Products are unavailable right now.']);
    }
}
