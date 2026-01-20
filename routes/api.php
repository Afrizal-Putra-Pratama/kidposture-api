<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\ScreeningController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/categories', [\App\Http\Controllers\Api\CategoryApiController::class, 'index']);
Route::get('/articles', [\App\Http\Controllers\Api\ArticleApiController::class, 'index']);
Route::get('/articles/{slug}', [\App\Http\Controllers\Api\ArticleApiController::class, 'show']);
// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',     [AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);

    Route::get('/children',  [ChildController::class, 'index']);
    Route::post('/children', [ChildController::class, 'store']);
    
    Route::get('/children/{child}/screenings',  [ScreeningController::class, 'index']);
    Route::post('/children/{child}/screenings', [ScreeningController::class, 'store']);

    Route::get('/screenings/{screening}', [ScreeningController::class, 'show']);

});

