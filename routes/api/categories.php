<?php

use App\Http\Controllers\Api\Category\IndexController as CategoryIndexController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', CategoryIndexController::class);
