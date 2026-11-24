<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\FeaturedProductsController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/featured-products', FeaturedProductsController::class);
Route::get('/categories', CategoriesController::class);
// Ids are small whole numbers; anything else cannot be a product, so it is a 404.
Route::get('/products/{id}', ProductController::class)->where('id', '[0-9]{1,9}');
Route::post('/products/{id}/enquiries', EnquiryController::class)->where('id', '[0-9]{1,9}');
