<?php

use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Cart\ClearController as CartClearController;
use App\Http\Controllers\Api\Cart\DestroyController as CartDestroyController;
use App\Http\Controllers\Api\Cart\IndexController as CartIndexController;
use App\Http\Controllers\Api\Cart\StoreController as CartStoreController;
use App\Http\Controllers\Api\Cart\UpdateController as CartUpdateController;
use App\Http\Controllers\Api\Chat\ShowController as ChatShowController;
use App\Http\Controllers\Api\Chat\StartController as ChatStartController;
use App\Http\Controllers\Api\Chat\StoreMessageController as ChatStoreMessageController;
use App\Http\Controllers\Api\Chat\UserIndexController as UserChatIndexController;
use App\Http\Controllers\Api\Order\IndexController as OrderIndexController;
use App\Http\Controllers\Api\Order\StoreController as OrderStoreController;
use App\Http\Controllers\Api\Review\CanReviewController;
use App\Http\Controllers\Api\Review\StoreController as ReviewStoreController;
use App\Http\Controllers\Api\Review\ToggleLikeController;
use App\Http\Controllers\Api\Wallet\TopUpController;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Support\Facades\Route;

Route::get('/me', MeController::class);
Route::post('/logout', LogoutController::class);
Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate']);
Route::post('/wallet/top-up', TopUpController::class);

Route::prefix('orders')->group(function (): void {
    Route::post('', OrderStoreController::class);
    Route::get('', OrderIndexController::class);
});

Route::prefix('reviews')->group(function (): void {
    Route::post('', ReviewStoreController::class);
    Route::post('{id}/like', ToggleLikeController::class);
});

Route::prefix('products')->group(function (): void {
    Route::get('{productId}/can-review', CanReviewController::class);
});

Route::prefix('chats')->group(function (): void {
    Route::get('', UserChatIndexController::class);
    Route::post('start', ChatStartController::class);
    Route::get('{chat}', ChatShowController::class)->middleware('chat.access');
    Route::post('{chat}/messages', ChatStoreMessageController::class)->middleware('chat.access');
});

Route::prefix('cart')->group(function (): void {
    Route::get('', CartIndexController::class);
    Route::post('', CartStoreController::class);
    Route::put('{id}', CartUpdateController::class);
    Route::delete('{id}', CartDestroyController::class);
    Route::delete('', CartClearController::class);
});
