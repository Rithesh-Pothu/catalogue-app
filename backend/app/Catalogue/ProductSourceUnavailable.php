<?php

namespace App\Catalogue;

use RuntimeException;

/**
 * The product source could not be read. The API answers 503 (see the exception
 * handler in bootstrap/app.php) so the client can say products are unavailable
 * and offer a retry, and never shows an empty catalogue as if nothing were for
 * sale.
 */
final class ProductSourceUnavailable extends RuntimeException {}
