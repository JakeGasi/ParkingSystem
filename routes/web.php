<?php

use App\Http\Controllers\ParkingController;
use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Authentication
require __DIR__.'/auth.php';

// Protected routes (require login)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [ParkingController::class, 'dashboard'])->name('dashboard');

    // User Map - Find Vehicle
    Route::get('/user-map', [ParkingController::class, 'userMap'])->name('user.map');
    Route::get('/api/search-vehicle', [ParkingController::class, 'searchVehicle'])->name('api.search');

    // Other pages - ADD NAMES TO THESE
    Route::get('/active-vehicles', [ParkingController::class, 'activeVehicles'])->name('active.vehicles');
    Route::get('/payment-history', [ParkingController::class, 'paymentHistory'])->name('payment.history');
    Route::get('/transactions', [ParkingController::class, 'transactions'])->name('transactions');
    Route::get('/slots', [ParkingController::class, 'slots'])->name('slots');

    // Check-in / Check-out
    Route::post('/checkin', [ParkingController::class, 'checkIn'])->name('checkin');
    Route::post('/checkout/{id}', [ParkingController::class, 'checkOut'])->name('checkout');

    // CRUD Operations
    Route::get('/transactions/{id}/edit', [ParkingController::class, 'editTransaction'])->name('transactions.edit');
    Route::put('/transactions/{id}', [ParkingController::class, 'updateTransaction'])->name('transactions.update');
    Route::delete('/transactions/{id}', [ParkingController::class, 'deleteTransaction'])->name('transactions.delete');

    // Slot Management
    Route::put('/slots/{id}', [ParkingController::class, 'updateSlot'])->name('slots.update');
    Route::delete('/slots/{id}', [ParkingController::class, 'deleteSlot'])->name('slots.delete');

    // API
    Route::get('/api/availability', [ParkingController::class, 'availability'])->name('api.availability');

    // Dummy profile routes to prevent errors
    Route::get('/profile', function() {
        return redirect('/dashboard');
    })->name('profile.edit');

    Route::patch('/profile', function() {
        return redirect('/dashboard');
    })->name('profile.update');

    Route::delete('/profile', function() {
        return redirect('/dashboard');
    })->name('profile.destroy');
});
require __DIR__.'/auth.php';
