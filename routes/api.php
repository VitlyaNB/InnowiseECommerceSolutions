<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Product\SearchProductAction;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);

Route::get('/products/search', SearchProductAction::class);
Route::get('/products/{id}', \App\Http\Controllers\Api\Product\GetProductByIdAction::class);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{categoryId}/products', \App\Http\Controllers\Api\Product\GetCategoryProductsAction::class);


Route::get('/cookie-consent', fn () => response()->json([
    'accepted' => request()->cookie('cookie_consent') === 'accepted',
]));

Route::post('/cookie-consent', function () {
    $accepted = request()->boolean('accepted');
    $cookie = cookie('cookie_consent', $accepted ? 'accepted' : 'declined', 365 * 24 * 60);

    return response()->json(['accepted' => $accepted])->cookie($cookie);
});

Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{id}', [CartController::class, 'update']);
Route::delete('/cart/{id}', [CartController::class, 'destroy']);
Route::delete('/cart', [CartController::class, 'clear']);

Route::middleware(['auth:sanctum', \App\Http\Middleware\CheckAdmin::class])->group(function () {
    Route::get('/users', [AuthController::class, 'index']);
    Route::put('/users/{id}', [AuthController::class, 'update']);
    Route::delete('/users/{id}', [AuthController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('/wallet/top-up', [AuthController::class, 'topUp']);
    Route::post('/categories/sync', [\App\Http\Controllers\Api\ExternalCategorySyncController::class, 'sync']);
    Route::post('/orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
});
