<?php

namespace Tests\Feature;

use App\Catalogue\InMemoryProductSource;
use App\Catalogue\Product;
use App\Catalogue\ProductSource;
use App\Catalogue\ProductSourceUnavailable;
use Tests\TestCase;

class SearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['catalogue.featured_product_ids' => [4]]);
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(1, 'Mens Cotton Jacket', 55.99, 'Great outerwear for the winter', "men's clothing", 'https://img.test/1.png', 4.7),
            new Product(2, 'Gold Bracelet', 695.0, 'Handmade, 100% real gold', 'jewelery', 'https://img.test/2.png', 4.6),
            new Product(3, 'Portable Hard Drive', 64.0, 'USB 3.0 and a COTTON carry pouch', 'electronics', 'https://img.test/3.png', 3.3),
            new Product(4, 'Cotton Dress', 9.85, 'A light dress', "women's clothing", 'https://img.test/4.png', 4.5),
        ]));
    }

    /**
     * @return list<int>
     */
    private function idsFor(string $term): array
    {
        return array_column($this->getJson('/api/search?'.http_build_query(['q' => $term]))->assertOk()->json('data'), 'id');
    }

    public function test_a_term_in_the_title_finds_the_product(): void
    {
        $this->assertSame([2], $this->idsFor('Bracelet'));
    }

    public function test_a_term_only_in_the_description_finds_the_product(): void
    {
        $this->assertSame([1], $this->idsFor('outerwear'));
    }

    public function test_matching_ignores_case_and_finds_title_and_description_matches_in_source_order(): void
    {
        // "cotton" is in the title of 1 and 4 (as "Cotton") and the description of 3 (as "COTTON").
        $this->assertSame([1, 3, 4], $this->idsFor('cOtToN'));
    }

    public function test_the_term_matches_inside_words_and_across_spaces(): void
    {
        $this->assertSame([2], $this->idsFor('old brace'));
        $this->assertSame([2], $this->idsFor('gold'));
    }

    public function test_surrounding_spaces_in_the_term_are_ignored(): void
    {
        $this->assertSame([2], $this->idsFor('  bracelet  '));
    }

    public function test_characters_in_the_term_are_matched_literally(): void
    {
        $this->assertSame([2], $this->idsFor('100%'));
        $this->assertSame([], $this->idsFor('%'.'%'.'%x'));
        $this->assertSame([], $this->idsFor('.*'));
    }

    public function test_matching_ignores_case_for_accented_letters_too(): void
    {
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(1, 'Café Table', 80.0, 'Wooden', 'furniture', 'https://img.test/1.png', null),
            new Product(2, 'Lamp', 20.0, 'Great for a CAFÉ corner', 'furniture', 'https://img.test/2.png', null),
        ]));

        $this->assertSame([1, 2], $this->idsFor('café'));
        $this->assertSame([1, 2], $this->idsFor('CAFÉ'));
    }

    public function test_a_term_with_no_matches_returns_no_products(): void
    {
        $this->getJson('/api/search?q=submarine')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_a_blank_term_returns_no_products_not_every_product(): void
    {
        foreach (['', '   ', "\t\n"] as $blank) {
            $this->assertSame([], $this->idsFor($blank));
        }
        $this->getJson('/api/search')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_a_featured_product_in_the_results_shows_no_rating(): void
    {
        $products = $this->getJson('/api/search?q=dress')->assertOk()->json('data');

        $this->assertSame([4], array_column($products, 'id'));
        $this->assertNull($products[0]['rating']);
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

        $this->getJson('/api/search?q=jacket')
            ->assertStatus(503)
            ->assertExactJson(['message' => 'Products are unavailable right now.']);
    }

    public function test_a_search_array_instead_of_a_string_is_treated_as_blank(): void
    {
        $this->getJson('/api/search?q[]=jacket')->assertOk()->assertExactJson(['data' => []]);
    }
}
