<?php

namespace App\Catalogue;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * The product source could not be read. The API answers 503 so the client can
 * say products are unavailable and offer a retry, and never shows an empty
 * catalogue as if nothing were for sale.
 */
class ProductSourceUnavailable extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => 'Products are unavailable right now.'], 503);
    }
}
