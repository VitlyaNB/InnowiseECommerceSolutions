<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

// Авторизация
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Базовый каталог
Route::get('/products', [ProductController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/categories/{categoryId}/products', \App\Http\Controllers\Api\Product\GetCategoryProductsAction::class);
Route::get('/products/{id}', \App\Http\Controllers\Api\Product\GetProductByIdAction::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [AuthController::class, 'index']);
    Route::post('/products', \App\Http\Controllers\Api\Product\StoreProductAction::class);

    // CRUD товаров
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']); // Используем POST для возможности загрузки файлов
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
