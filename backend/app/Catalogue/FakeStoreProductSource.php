<?php

namespace App\Catalogue;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Reads products from the Fake Store API. This is the only class that knows
 * that source's field names; everything else sees our own Product.
 */
final class FakeStoreProductSource implements ProductSource
{
    private const REQUIRED_FIELDS = ['id', 'title', 'price', 'description', 'category', 'image'];

    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeoutSeconds,
    ) {}

    public function all(): array
    {
        try {
            $items = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeoutSeconds)
                ->acceptJson()
                ->get('/products')
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $exception) {
            throw new ProductSourceUnavailable('Could not read products from Fake Store.', 0, $exception);
        }

        if (! is_array($items)) {
            throw new ProductSourceUnavailable('Fake Store did not return a list of products.');
        }

        return array_map($this->toProduct(...), array_values($items));
    }

    /**
     * @param  mixed  $item
     */
    private function toProduct($item): Product
    {
        if (! is_array($item) || array_diff(self::REQUIRED_FIELDS, array_keys($item)) !== []) {
            throw new ProductSourceUnavailable('Fake Store returned a product we could not read.');
        }

        return new Product(
            id: (int) $item['id'],
            title: (string) $item['title'],
            price: (float) $item['price'],
            description: (string) $item['description'],
            category: (string) $item['category'],
            image: (string) $item['image'],
            rating: isset($item['rating']['rate']) ? (float) $item['rating']['rate'] : null,
        );
    }
}
