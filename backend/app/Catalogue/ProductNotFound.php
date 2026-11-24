<?php

namespace App\Catalogue;

use RuntimeException;

/**
 * The catalogue has no product with that id. The API answers 404 (see the
 * exception handler in bootstrap/app.php).
 */
final class ProductNotFound extends RuntimeException {}
