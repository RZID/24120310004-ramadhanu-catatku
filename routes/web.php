<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('transactions', TransactionController::class)->except('show');

    Route::resource('wallets', WalletController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');

    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
