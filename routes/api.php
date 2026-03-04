<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Product\SearchProductAction;
use App\Http\Controllers\Api\Product\GetProductByIdAction;
use App\Http\Controllers\Api\Product\GetCategoryProductsAction;

// Авторизация
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- ТОВАРЫ ---
// 1. Сначала поиск (ЭТО ВАЖНО, чтобы не было 404)
Route::get('/products/search', SearchProductAction::class);

// 2. Потом список и конкретный товар
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', GetProductByIdAction::class);

// --- КАТЕГОРИИ ---
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{categoryId}/products', GetCategoryProductsAction::class);

// Куки
Route::get('/cookie-consent', fn () => response()->json(['accepted' => request()->cookie('cookie_consent') === 'accepted']));
Route::post('/cookie-consent', function () {
    $cookie = cookie('cookie_consent', request()->boolean('accepted') ? 'accepted' : 'declined', 365 * 24 * 60);
    return response()->json(['accepted' => request()->boolean('accepted')])->cookie($cookie);
});

// --- ЗАЩИЩЕННЫЕ МАРШРУТЫ ---
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Пополнение и Заказы
    Route::post('/wallet/top-up', [AuthController::class, 'topUp']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Админка
    Route::middleware([\App\Http\Middleware\CheckAdmin::class])->group(function () {
        Route::get('/users', [AuthController::class, 'index']);
        Route::put('/users/{id}', [AuthController::class, 'update']);
        Route::delete('/users/{id}', [AuthController::class, 'destroy']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        Route::post('/categories/sync', [\App\Http\Controllers\Api\ExternalCategorySyncController::class, 'sync']);
    });
});
