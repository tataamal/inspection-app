<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SemarangController;
use App\Http\Controllers\SurabayaController;
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
    
    Route::get('/semarang-inspect',[InspectionController::class, 'InspectionSemarang'])->name('inspect-semarang');

    // routing halaman yang hanya bisa diakses oleh admin
    Route::get('/admin-create', [AdminController::class, 'create'])->name('create-question')->middleware('role:admin');
    Route::get('/admin-edit', [AdminController::class, 'edit'])->name('create-edit')->middleware('role:admin');

    // routing halaman ke semarang
    Route::get('/semarang-pro',[SemarangController::class, 'InspectionLotSemarang'])->name('PRO-semarang');
    Route::get('/semarang-do',[SemarangController::class, 'TaskDOSemarang'])->name('DO-semarang');
    Route::get('/semarang-activity',[SemarangController::class, 'ActivitySemarang'])->name('Activity-semarang');
    Route::get('/semarang-inspection/{aufnr}', [SemarangController::class, 'ActionButton'])->name('inspection-semarang');

    // routing halaman ke surabaya
    Route::get('/surabaya-pro',[SurabayaController::class, 'InspectionLotSurabaya'])->name('PRO-surabaya');
    Route::get('/surabaya-do',[SurabayaController::class, 'TaskDOSurabaya'])->name('DO-surabaya');
    Route::get('/surabaya-activity',[SemarangController::class, 'ActivitySurabaya'])->name('Activity-surabaya');
    Route::get('/surabaya-inspection/{aufnr}', [SurabayaController::class, 'ActionButton'])->name('inspection-surabaya');

    // routing untuk logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});


