<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RentSureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [
    RentSureController::class,
    'home',
])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [
        RentSureController::class,
        'dashboard',
    ])->name('dashboard');

    Route::post('/properties', [
        RentSureController::class,
        'storeProperty',
    ])->name('properties.store');

    Route::patch('/properties/{property}/availability', [
        RentSureController::class,
        'toggleProperty',
    ])->name('properties.toggle');

    Route::post('/verification-documents', [
        RentSureController::class,
        'submitVerification',
    ])->name('verification.store');

    Route::get('/verification-documents/{document}/download', [
        RentSureController::class,
        'downloadVerification',
    ])->name('verification.download');

    Route::post('/tenancies', [
        RentSureController::class,
        'storeTenancy',
    ])->name('tenancies.store');

    Route::post('/payment-receipts', [
        RentSureController::class,
        'storePayment',
    ])->name('payments.store');

    Route::post('/fraud-reports', [
        RentSureController::class,
        'submitFraudReport',
    ])->name('fraud-reports.store');

    Route::get('/profile', [
        ProfileController::class,
        'edit',
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update',
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy',
    ])->name('profile.destroy');
});

Route::middleware([
    'auth',
    'role:administrator',
])->group(function () {
    Route::patch('/verification-documents/{document}/review', [
        RentSureController::class,
        'reviewVerification',
    ])->name('verification.review');

    Route::patch('/fraud-reports/{report}/review', [
        RentSureController::class,
        'reviewFraudReport',
    ])->name('fraud-reports.review');
});

require __DIR__.'/auth.php';