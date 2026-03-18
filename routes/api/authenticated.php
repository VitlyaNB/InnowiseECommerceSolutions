<?php

use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\TopUpController;
use App\Http\Controllers\Api\Chat\ShowController as ChatShowController;
use App\Http\Controllers\Api\Chat\StartController as ChatStartController;
use App\Http\Controllers\Api\Chat\StoreMessageController as ChatStoreMessageController;
use App\Http\Controllers\Api\Chat\UserIndexController as UserChatIndexController;
use App\Http\Controllers\Api\Order\IndexController as OrderIndexController;
use App\Http\Controllers\Api\Order\StoreController as OrderStoreController;
use App\Http\Controllers\Api\Review\CanReviewController;
use App\Http\Controllers\Api\Review\StoreController as ReviewStoreController;
use App\Http\Controllers\Api\Review\ToggleLikeController;
use Illuminate\Support\Facades\Route;

Route::get('/me', MeController::class);
Route::post('/logout', LogoutController::class);
Route::post('/wallet/top-up', TopUpController::class);
Route::post('/orders', OrderStoreController::class);
Route::get('/orders', OrderIndexController::class);
Route::post('/reviews', ReviewStoreController::class);
Route::post('/reviews/{id}/like', ToggleLikeController::class);
Route::get('/products/{productId}/can-review', CanReviewController::class);
Route::get('/chats', UserChatIndexController::class);
Route::get('/chats/{chat}', ChatShowController::class)->middleware('chat.access');
Route::post('/chats/{chat}/messages', ChatStoreMessageController::class)->middleware('chat.access');
Route::post('/chats/start', ChatStartController::class);
