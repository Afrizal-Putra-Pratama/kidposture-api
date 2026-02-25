<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\PhysiotherapistController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ArticleCategoryController; // Controller yang akan kita buat

Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes (protected by auth middleware)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // CMS artikel edukasi
    Route::resource('articles', ArticleController::class);

    // Kategori Artikel (Supaya menu Sidebar Kategori tidak error)
    Route::resource('categories', ArticleCategoryController::class);

    // Manajemen fisioterapis (list + detail + verifikasi)
    Route::resource('physiotherapists', PhysiotherapistController::class)
        ->only(['index', 'show', 'edit', 'update', 'destroy']);

    Route::post('physiotherapists/{physiotherapist}/approve', [PhysiotherapistController::class, 'approve'])
        ->name('physiotherapists.approve');

    Route::post('physiotherapists/{physiotherapist}/reject', [PhysiotherapistController::class, 'reject'])
        ->name('physiotherapists.reject');
        
    // Manajemen Users / Orang Tua
    Route::resource('users', UserController::class)
        ->only(['index', 'show', 'destroy']);
});