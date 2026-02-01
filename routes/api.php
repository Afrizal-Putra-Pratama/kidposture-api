<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\ScreeningController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ArticleApiController;
use App\Http\Controllers\Api\PhysiotherapistController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PhysioProfileController;
use App\Http\Controllers\Api\AdminPhysioController;
use App\Http\Controllers\Api\PhysioArticleController;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/physio', [AuthController::class, 'registerPhysio']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/articles', [ArticleApiController::class, 'index']);
Route::get('/articles/{slug}', [ArticleApiController::class, 'show']);

// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Parent: children & screenings
    Route::get('/children', [ChildController::class, 'index']);
    Route::post('/children', [ChildController::class, 'store']);
    Route::put('/children/{child}', [ChildController::class, 'update']);
    Route::delete('/children/{child}', [ChildController::class, 'destroy']);

    Route::get('/children/{child}/screenings', [ScreeningController::class, 'index']);
    Route::post('/children/{child}/screenings', [ScreeningController::class, 'store']);

    // Detail screening
    Route::get('/screenings/{screening}', [ScreeningController::class, 'show']);

    // Parent rujuk ke fisio
    Route::post('/screenings/{screening}/refer', [ScreeningController::class, 'referToPhysio']);

    // Direktori fisioterapis (untuk parent pilih fisio)
    Route::get('/physiotherapists', [PhysiotherapistController::class, 'index']);
    Route::get('/physiotherapists/{id}', [PhysiotherapistController::class, 'show']);

    // Notifications (parent)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'destroyAll']);
    });

    // Rekomendasi manual (fisio only)
    Route::middleware('role:physio')->group(function () {
        Route::post('/screenings/{screening}/recommendations', [ScreeningController::class, 'storeRecommendation']);
        Route::get('/screenings/{screening}/recommendations', [ScreeningController::class, 'getRecommendations']);
    });

    // Physio-only routes
    Route::prefix('physio')->middleware('role:physio')->group(function () {
        Route::get('/profile', [PhysioProfileController::class, 'show']);
        Route::post('/profile', [PhysioProfileController::class, 'update']);
        
        Route::get('/screenings', [ScreeningController::class, 'physioIndex']);
        Route::get('/referrals', [ScreeningController::class, 'myReferrals']);
        Route::patch('/referrals/{screening}/status', [ScreeningController::class, 'updateReferralStatus']);

        // Article Management
        Route::apiResource('articles', PhysioArticleController::class);
    });

    // Admin-only routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/physiotherapists', [AdminPhysioController::class, 'index']);
        Route::patch('/physiotherapists/{id}/approve', [AdminPhysioController::class, 'approve']);
        Route::patch('/physiotherapists/{id}/reject', [AdminPhysioController::class, 'reject']);
    });
});
