<?php

use App\Http\Controllers\AuthGoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/leads', [LeadController::class, 'store'])->middleware('throttle:30,1')->name('leads.store');

Route::get('/auth/google', [AuthGoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [AuthGoogleController::class, 'callback']);

Route::middleware('auth')->group(function () {
    Route::get('/services-new', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::patch('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
