<?php

use App\Http\Middleware\CheckAdmin;
use Illuminate\Support\Facades\Route;

require __DIR__.'/api/auth.php';
require __DIR__.'/api/products.php';
require __DIR__.'/api/categories.php';
require __DIR__.'/api/recommendations.php';
require __DIR__.'/api/cookies.php';

Route::middleware(['auth:sanctum', CheckAdmin::class])->group(__DIR__.'/api/admin.php');
Route::middleware('auth:sanctum')->group(__DIR__.'/api/authenticated.php');
