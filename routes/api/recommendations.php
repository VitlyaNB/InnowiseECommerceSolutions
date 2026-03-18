<?php

use App\Http\Controllers\Api\Recommendation\HomeController as RecommendationHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/recommendations/home', RecommendationHomeController::class);
