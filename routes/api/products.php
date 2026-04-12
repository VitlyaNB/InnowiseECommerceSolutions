<?php

use App\Http\Controllers\Api\Product\CategoryProductsController;
use App\Http\Controllers\Api\Product\IndexController as ProductIndexController;
use App\Http\Controllers\Api\Product\SearchController as ProductSearchController;
use App\Http\Controllers\Api\Product\ShowController as ProductShowController;
use App\Http\Controllers\Api\Recommendation\ProductController as ProductRecommendationsController;
use App\Http\Controllers\Api\Recommendation\ViewController as ProductViewController;
use App\Http\Controllers\Api\Review\IndexController as ProductReviewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function (): void {
    Route::get('search', ProductSearchController::class);
    Route::get('', ProductIndexController::class);
    Route::get('{id}', ProductShowController::class);
    Route::get('{id}/recommendations', ProductRecommendationsController::class);
    Route::post('{id}/view', ProductViewController::class);
    Route::get('{id}/reviews', ProductReviewsController::class);
});

Route::get('categories/{categoryId}/products', CategoryProductsController::class);
