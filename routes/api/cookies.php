<?php

use App\Http\Controllers\Api\CookieConsent\ShowController as CookieConsentShowController;
use App\Http\Controllers\Api\CookieConsent\StoreController as CookieConsentStoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('cookie-consent')->group(function (): void {
    Route::get('', CookieConsentShowController::class);
    Route::post('', CookieConsentStoreController::class);
});
