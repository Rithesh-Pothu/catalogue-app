<?php

namespace App\Http\Controllers;

use App\Catalogue\Catalogue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, Catalogue $catalogue): JsonResponse
    {
        // Anything that is not a plain string (such as ?q[]=x) is a blank term.
        $term = $request->query('q');
        $term = is_string($term) ? $term : '';

        return $this->productList($catalogue->search($term));
    }
}
