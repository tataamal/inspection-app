<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InspectionController;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    // Ini yang akan dipanggil oleh Vue (form.post('/login'))
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes
Route::middleware('auth_custom')->group(function () { // Nanti kita buat middleware custom cek Redis
    Route::get('/dashboard', function () {
        return inertia('Dashboard'); 
    })->name('dashboard');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth_custom'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/inspection/{dispo}', [InspectionController::class, 'index'])->name('inspection.index');
Route::get('/inspection/form/{lotNumber}', [InspectionController::class, 'showForm'])->name('inspection.form');
Route::post('/inspection/submit', [InspectionController::class, 'store'])->name('inspection.submit');