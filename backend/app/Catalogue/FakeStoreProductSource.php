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
            $rows = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeoutSeconds)
                ->acceptJson()
                ->get('/products')
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $exception) {
            throw new ProductSourceUnavailable('Could not read products from Fake Store.', 0, $exception);
        }

        if (! is_array($rows)) {
            throw new ProductSourceUnavailable('Fake Store did not return a list of products.');
        }

        return array_map($this->toProduct(...), array_values($rows));
    }

    /**
     * @param  mixed  $row  one product exactly as Fake Store describes it
     */
    private function toProduct($row): Product
    {
        if (! is_array($row) || array_diff(self::REQUIRED_FIELDS, array_keys($row)) !== []) {
            throw new ProductSourceUnavailable('Fake Store returned a product we could not read.');
        }

        return new Product(
            id: (int) $row['id'],
            title: (string) $row['title'],
            price: (float) $row['price'],
            description: (string) $row['description'],
            category: (string) $row['category'],
            image: (string) $row['image'],
            rating: isset($row['rating']['rate']) ? (float) $row['rating']['rate'] : null,
        );
    }
}
