<?php

use App\Http\Controllers\Api\CookieConsent\ShowController as CookieConsentShowController;
use App\Http\Controllers\Api\CookieConsent\StoreController as CookieConsentStoreController;
use Illuminate\Support\Facades\Route;

Route::get('/cookie-consent', CookieConsentShowController::class);
Route::post('/cookie-consent', CookieConsentStoreController::class);
