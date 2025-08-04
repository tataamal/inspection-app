<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InspectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});

Route::get('dashboard', [InspectionController::class, 'Dashboard'])->name('dashboard');
Route::get('semarang-pro',[InspectionController::class, 'ProSemarang'])->name('PRO-semarang');
Route::get('semarang-do',[InspectionController::class, 'DOSemarang'])->name('DO-semarang');
Route::get('semarang-inspect',[InspectionController::class, 'InspectionSemarang'])->name('inspect-semarang');
Route::get('semarang-activity',[InspectionController::class, 'ActivitySemarang'])->name('activity-semarang');
Route::get('admin-create', [AdminController::class, 'create'])->name('create-question');
Route::get('admin-edit', [AdminController::class, 'edit'])->name('create-edit');