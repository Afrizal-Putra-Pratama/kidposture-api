<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\ScreeningController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ArticleApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// 🔓 Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/articles',   [ArticleApiController::class, 'index']);
Route::get('/articles/{slug}', [ArticleApiController::class, 'show']);

// 🔐 Protected routes (butuh token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Parent: children & screenings
    Route::get('/children',  [ChildController::class, 'index']);
    Route::post('/children', [ChildController::class, 'store']);
    Route::put('/children/{child}',    [ChildController::class, 'update']);
    Route::delete('/children/{child}', [ChildController::class, 'destroy']);

    Route::get('/children/{child}/screenings',  [ScreeningController::class, 'index']);
    Route::post('/children/{child}/screenings', [ScreeningController::class, 'store']);

    // ✅ Detail screening (parent & fisio bisa akses)
    Route::get('/screenings/{screening}', [ScreeningController::class, 'show']);

    // ✅ Rekomendasi manual fisioterapis (hanya role:physio)
    Route::middleware('role:physio')->group(function () {
        Route::post('/screenings/{screening}/recommendations', [ScreeningController::class, 'storeRecommendation']);
        Route::get('/screenings/{screening}/recommendations', [ScreeningController::class, 'getRecommendations']);
    });

    // Physio-only routes: butuh auth + role:physio
    Route::prefix('physio')
        ->middleware('role:physio')
        ->group(function () {
            Route::get('/screenings', [ScreeningController::class, 'physioIndex']);
        });
});
