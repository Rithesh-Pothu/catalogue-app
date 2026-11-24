<?php

namespace App\Catalogue;

use RuntimeException;

/**
 * The catalogue has no products in that category. The API answers 404 (see the
 * exception handler in bootstrap/app.php).
 */
final class CategoryNotFound extends RuntimeException {}
