<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// routing halaman default
Route::get('/', function () {
    return view('index');
});

Route::get('/errors-404', [LoginController::class, 'ErrorsPage'])->name('errors-404');



// routing halaman tanpa autentikasi
Route::middleware(['guest'])->group(function() {
    
    Route::get('/login', [LoginController::class, 'index'])->name('login-index');
    Route::post('/submit-login', [LoginController::class, 'login'])->name('submit-login');
});

// routing halaman dengan autentikasi
Route::middleware(['auth'])->group(function () {

    // routing halaman yang bisa diakses semua role user
    Route::get('/dashboard', [InspectionController::class, 'Dashboard'])->name('dashboard');
    Route::get('/semarang-pro',[InspectionController::class, 'ProSemarang'])->name('PRO-semarang');
    Route::get('/semarang-do',[InspectionController::class, 'DOSemarang'])->name('DO-semarang');
    Route::get('/semarang-inspect',[InspectionController::class, 'InspectionSemarang'])->name('inspect-semarang');
    Route::get('/semarang-activity',[InspectionController::class, 'ActivitySemarang'])->name('activity-semarang');

    // routing halaman yang hanya bisa diakses oleh admin
    Route::get('/admin-create', [AdminController::class, 'create'])->name('create-question')->middleware('role:admin');
    Route::get('/admin-edit', [AdminController::class, 'edit'])->name('create-edit')->middleware('role:admin');

    // routing untuk logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});


