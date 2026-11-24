<?php

namespace App\Http\Controllers;

use App\Catalogue\Catalogue;
use App\Catalogue\Product;
use Illuminate\Http\JsonResponse;

class CategoryProductsController extends Controller
{
    public function __invoke(Catalogue $catalogue, string $category): JsonResponse
    {
        return response()->json([
            'data' => array_map(fn (Product $product) => $product->toArray(), $catalogue->inCategory($category)),
        ]);
    }
}
