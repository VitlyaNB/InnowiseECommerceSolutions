<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/categories/{categoryId}/products', \App\Http\Controllers\Api\Product\GetCategoryProductsAction::class);
Route::get('/products/{id}', \App\Http\Controllers\Api\Product\GetProductByIdAction::class);

// Защищенные админские маршруты
Route::middleware(['auth:sanctum', \App\Http\Middleware\CheckAdmin::class])->group(function () {
    // Пользователи
    Route::get('/users', [AuthController::class, 'index']);
    Route::put('/users/{id}', [AuthController::class, 'update']);
    Route::delete('/users/{id}', [AuthController::class, 'destroy']);

    // Товары
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
