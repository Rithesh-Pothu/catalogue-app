<?php

namespace App\Http\Controllers;

use App\Catalogue\Catalogue;
use Illuminate\Http\JsonResponse;

class FeaturedProductsController extends Controller
{
    public function __invoke(Catalogue $catalogue): JsonResponse
    {
        return $this->productList($catalogue->featured());
    }
}
