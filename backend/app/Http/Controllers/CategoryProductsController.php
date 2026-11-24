<?php

namespace App\Http\Controllers;

use App\Catalogue\Catalogue;
use Illuminate\Http\JsonResponse;

class CategoryProductsController extends Controller
{
    public function __invoke(Catalogue $catalogue, string $category): JsonResponse
    {
        return $this->productList($catalogue->inCategory($category));
    }
}
