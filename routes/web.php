<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\PhysiotherapistController;

Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes (protected by auth middleware)
// kalau nanti mau bedakan admin/fisio, tambahkan middleware is_admin di sini
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // CMS artikel edukasi
    Route::resource('articles', ArticleController::class);

    // Manajemen fisioterapis (list + detail + verifikasi)
    Route::resource('physiotherapists', PhysiotherapistController::class)
        ->only(['index', 'show', 'edit', 'update', 'destroy']);

    Route::post('physiotherapists/{physiotherapist}/approve', [PhysiotherapistController::class, 'approve'])
        ->name('physiotherapists.approve');

    Route::post('physiotherapists/{physiotherapist}/reject', [PhysiotherapistController::class, 'reject'])
        ->name('physiotherapists.reject');
});
