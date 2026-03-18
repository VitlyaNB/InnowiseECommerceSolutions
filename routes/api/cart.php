<?php

use App\Http\Controllers\Api\Cart\ClearController as CartClearController;
use App\Http\Controllers\Api\Cart\DestroyController as CartDestroyController;
use App\Http\Controllers\Api\Cart\IndexController as CartIndexController;
use App\Http\Controllers\Api\Cart\StoreController as CartStoreController;
use App\Http\Controllers\Api\Cart\UpdateController as CartUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/cart', CartIndexController::class);
Route::post('/cart', CartStoreController::class);
Route::put('/cart/{id}', CartUpdateController::class);
Route::delete('/cart/{id}', CartDestroyController::class);
Route::delete('/cart', CartClearController::class);
