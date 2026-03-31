<?php

use App\Http\Controllers\Api\Admin\User\DeleteController as AdminUserDeleteController;
use App\Http\Controllers\Api\Admin\User\IndexController as AdminUserIndexController;
use App\Http\Controllers\Api\Admin\User\UpdateController as AdminUserUpdateController;
use App\Http\Controllers\Api\Category\DestroyController as CategoryDestroyController;
use App\Http\Controllers\Api\Category\StoreController as CategoryStoreController;
use App\Http\Controllers\Api\Category\SyncController as CategorySyncController;
use App\Http\Controllers\Api\Category\UpdateController as CategoryUpdateController;
use App\Http\Controllers\Api\Chat\AdminIndexController as AdminChatIndexController;
use App\Http\Controllers\Api\Product\DestroyController as ProductDestroyController;
use App\Http\Controllers\Api\Product\StoreController as ProductStoreController;
use App\Http\Controllers\Api\Product\UpdateController as ProductUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/users', AdminUserIndexController::class);
Route::put('/users/{id}', AdminUserUpdateController::class);
Route::delete('/users/{id}', AdminUserDeleteController::class);
Route::get('/admin/chats', AdminChatIndexController::class);
Route::post('/products', ProductStoreController::class);
Route::put('/products/{id}', ProductUpdateController::class);
Route::patch('/products/{id}', ProductUpdateController::class);
Route::post('/products/{id}', ProductUpdateController::class);
Route::delete('/products/{id}', ProductDestroyController::class);
Route::post('/categories', CategoryStoreController::class);
Route::put('/categories/{id}', CategoryUpdateController::class);
Route::patch('/categories/{id}', CategoryUpdateController::class);
Route::post('/categories/{id}', CategoryUpdateController::class);
Route::delete('/categories/{id}', CategoryDestroyController::class);
Route::post('/categories/sync', CategorySyncController::class);
