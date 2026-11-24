<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\FeaturedProductsController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/featured-products', FeaturedProductsController::class);
Route::get('/categories', CategoriesController::class);
Route::get('/products/{id}', ProductController::class)->whereNumber('id');
