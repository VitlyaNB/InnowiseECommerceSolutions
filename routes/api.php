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
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\ReviewController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- ТОВАРЫ ---
Route::get('/products/search', SearchProductAction::class);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', GetProductByIdAction::class);
Route::get('/products/{id}/recommendations', [RecommendationController::class, 'product']);
Route::post('/products/{id}/view', [RecommendationController::class, 'view']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);

// Homepage recommendations
Route::get('/recommendations/home', [RecommendationController::class, 'home']);

// --- КАТЕГОРИИ ---
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{categoryId}/products', GetCategoryProductsAction::class);

// --- КОРЗИНА (Теперь доступна всем) ---
// Мы вынесли её из middleware auth:sanctum, чтобы работала для гостей
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{id}', [CartController::class, 'update']);
Route::delete('/cart/{id}', [CartController::class, 'destroy']);
Route::delete('/cart', [CartController::class, 'clear']);

// Cookie Consent
Route::get('/cookie-consent', fn () => response()->json(['accepted' => request()->cookie('cookie_consent') === 'accepted']));
Route::post('/cookie-consent', function () {
    $cookie = cookie('cookie_consent', request()->boolean('accepted') ? 'accepted' : 'declined', 365 * 24 * 60);
    return response()->json(['accepted' => request()->boolean('accepted')])->cookie($cookie);
});

// --- ТОЛЬКО АВТОРИЗОВАННЫЕ ---
Route::middleware(['auth:sanctum'])->group(function () {
    // Кошелек и Заказы (покупать могут только авторизованные)
    Route::post('/wallet/top-up', [AuthController::class, 'topUp']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);

    // Отзывы
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/reviews/{id}/like', [ReviewController::class, 'like']);
    Route::get('/products/{id}/can-review', [ReviewController::class, 'checkPermission']);

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
