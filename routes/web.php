<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InspectionController;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ✅ MASUKKAN ROUTE INSPECTION KE DALAM MIDDLEWARE AGAR AMAN
Route::middleware('auth_custom')->group(function () { 
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Inspection Routes (Pindahkan ke sini)
    Route::get('/inspection/components/{aufnr}', [InspectionController::class, 'getComponents']);
    Route::get('/inspection/form/{lotNumber}', [InspectionController::class, 'showForm'])->name('inspection.form');
    Route::post('/inspection/submit', [InspectionController::class, 'store'])->name('inspection.submit');
    
    // Route Index sebaiknya ditaruh paling bawah jika menggunakan parameter dinamis agar tidak bentrok
    Route::get('/inspection/{dispo}', [InspectionController::class, 'index'])->name('inspection.index');
});