<?php

namespace App\Http\Controllers;

use App\Catalogue\Catalogue;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __invoke(Catalogue $catalogue, int $id): JsonResponse
    {
        return response()->json(['data' => $catalogue->product($id)->toArray()]);
    }
}
