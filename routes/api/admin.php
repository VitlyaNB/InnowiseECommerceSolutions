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

Route::prefix('users')->group(function (): void {
    Route::get('', AdminUserIndexController::class);
    Route::put('{id}', AdminUserUpdateController::class);
    Route::delete('{id}', AdminUserDeleteController::class);
});

Route::prefix('chats')->group(function (): void {
    Route::get('', AdminChatIndexController::class);
});

Route::prefix('products')->group(function (): void {
    Route::post('', ProductStoreController::class);
    Route::put('{id}', ProductUpdateController::class);
    Route::patch('{id}', ProductUpdateController::class);
    Route::post('{id}', ProductUpdateController::class);
    Route::delete('{id}', ProductDestroyController::class);
});

Route::prefix('categories')->group(function (): void {
    Route::post('', CategoryStoreController::class);
    Route::put('{id}', CategoryUpdateController::class);
    Route::patch('{id}', CategoryUpdateController::class);
    Route::post('{id}', CategoryUpdateController::class);
    Route::delete('{id}', CategoryDestroyController::class);
    Route::post('sync', CategorySyncController::class);
});
