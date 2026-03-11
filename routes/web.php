<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PltsController;
use App\Http\Controllers\ListrikSs4Controller; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrendController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () { return redirect()->route('login'); });

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login-process', [LoginController::class, 'login'])->name('login.process');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware(['auth'])->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- PLTS ---
    Route::get('/plts', [PltsController::class, 'index'])->name('plts.index');
    Route::post('/plts/bulk-update', [PltsController::class, 'bulkUpdate'])->name('plts.bulkUpdate');
    Route::post('/plts/upload', [PltsController::class, 'upload'])->name('plts.upload'); 
    Route::post('/plts/update', [PltsController::class, 'update'])->name('plts.update');
    // Di dalam group auth
    Route::post('/plts/delete', [PltsController::class, 'destroy'])->name('plts.delete');

    // --- LISTRIK SS-4 (SINKRON DENGAN CONTROLLER BARU) ---
    Route::get('/ss4', [ListrikSs4Controller::class, 'index'])->name('ss4.index');
    Route::post('/ss4/upload', [ListrikSs4Controller::class, 'upload'])->name('ss4.upload');
    Route::post('/ss4/bulk-update', [ListrikSs4Controller::class, 'bulkUpdate'])->name('ss4.bulkUpdate');
    Route::post('/ss4/delete', [ListrikSs4Controller::class, 'destroy'])->name('ss4.delete'); // <--- INI WAJIB ADA BUAT HAPUS

    // --- MANAJEMEN USER ---
    Route::resource('users', UserController::class);

    // --- TREND (SAYA RAPIKAN MASUK SINI) ---
    Route::get('/trend', [TrendController::class, 'index'])->name('trend.index');
    Route::post('/trend/upload', [TrendController::class, 'upload'])->name('trend.upload');
    Route::post('/trend/update', [TrendController::class, 'bulkUpdate'])->name('trend.bulkUpdate');
    Route::post('/trend/store', [TrendController::class, 'store'])->name('trend.store');
    // Route untuk hapus data Trend (Mass Delete)
    Route::post('/trend/destroy', [App\Http\Controllers\TrendController::class, 'destroy'])->name('trend.destroy');
   
});