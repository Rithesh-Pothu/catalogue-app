<?php

namespace App\Http\Controllers;

use App\Catalogue\Product;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * The JSON every endpoint that lists products answers with, so the shape
     * the frontend relies on is defined once.
     *
     * @param  list<Product>  $products
     */
    protected function productList(array $products): JsonResponse
    {
        return response()->json([
            'data' => array_map(fn (Product $product) => $product->toArray(), $products),
        ]);
    }
}
