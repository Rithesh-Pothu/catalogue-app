<?php

namespace App\Http\Controllers;

use App\Catalogue\Catalogue;
use Illuminate\Http\JsonResponse;

class CategoriesController extends Controller
{
    public function __invoke(Catalogue $catalogue): JsonResponse
    {
        return response()->json(['data' => $catalogue->categories()]);
    }
}
