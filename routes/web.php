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
Route::middleware('auth_custom')->group(function () { 
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/inspection/components/{aufnr}', [InspectionController::class, 'getComponents']);
    Route::get('/inspection/form/{lotNumber}', [InspectionController::class, 'showForm'])->name('inspection.form');
    Route::post('/inspection/submit', [InspectionController::class, 'store'])->name('inspection.submit');
    Route::get('/inspection/{dispo}', [InspectionController::class, 'index'])->name('inspection.index');
    Route::post('/inspection/bulk-pass', [InspectionController::class, 'bulkPass'])->name('inspection.bulk_pass');
});