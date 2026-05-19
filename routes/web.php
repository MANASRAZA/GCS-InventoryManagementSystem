<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Livewire\PurchaseList;
use App\Livewire\PurchaseForm;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/', function () {
        return redirect()->route('purchases.index');
    });

    // Purchases View (Admin and User)
    Route::get('/purchases', PurchaseList::class)->name('purchases.index');

    // Purchases Create & Edit Actions (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/purchases/create', PurchaseForm::class)->name('purchases.create');
        Route::get('/purchases/{purchase}/edit', PurchaseForm::class)->name('purchases.edit');
    });
});
