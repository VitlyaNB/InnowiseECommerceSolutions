<?php

use App\Http\Controllers\Api\Product\CategoryProductsController;
use App\Http\Controllers\Api\Product\IndexController as ProductIndexController;
use App\Http\Controllers\Api\Product\SearchController as ProductSearchController;
use App\Http\Controllers\Api\Product\ShowController as ProductShowController;
use App\Http\Controllers\Api\Recommendation\ProductController as ProductRecommendationsController;
use App\Http\Controllers\Api\Recommendation\ViewController as ProductViewController;
use App\Http\Controllers\Api\Review\IndexController as ProductReviewsController;
use Illuminate\Support\Facades\Route;

Route::get('/products/search', ProductSearchController::class);
Route::get('/products', ProductIndexController::class);
Route::get('/products/{id}', ProductShowController::class);
Route::get('/products/{id}/recommendations', ProductRecommendationsController::class);
Route::post('/products/{id}/view', ProductViewController::class);
Route::get('/products/{productId}/reviews', ProductReviewsController::class);
Route::get('/categories/{categoryId}/products', CategoryProductsController::class);
