<?php

use App\Http\Controllers\ParkingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes (login, register, logout, password reset)
require __DIR__.'/auth.php';

// ==================== PROTECTED ROUTES (Require Login) ====================
Route::middleware(['auth'])->group(function () {

    // ==================== MAIN DASHBOARD ====================
    Route::get('/dashboard', [ParkingController::class, 'dashboard'])->name('dashboard');

    // ==================== USER OVERVIEW - 50 SLOTS WITH FLOOR VIEW ====================
    Route::get('/user-overview', [ParkingController::class, 'userOverview'])->name('user.overview');

    // ==================== USER MAP - FIND VEHICLE ====================
    Route::get('/user-map', [ParkingController::class, 'userMap'])->name('user.map');
    Route::get('/user-view', [ParkingController::class, 'userView'])->name('user.view');
    Route::get('/api/search-vehicle', [ParkingController::class, 'searchVehicle'])->name('api.search');

    // ==================== PARKING PAGES ====================
    Route::get('/active-vehicles', [ParkingController::class, 'activeVehicles'])->name('active.vehicles');
    Route::get('/payment-history', [ParkingController::class, 'paymentHistory'])->name('payment.history');
    Route::get('/transactions', [ParkingController::class, 'transactions'])->name('transactions');
    Route::get('/slots', [ParkingController::class, 'slots'])->name('slots');

    // ==================== CHECK-IN / CHECK-OUT ====================
    Route::post('/checkin', [ParkingController::class, 'checkIn'])->name('checkin');
    Route::post('/checkout/{id}', [ParkingController::class, 'checkOut'])->name('checkout');

    // ==================== CRUD OPERATIONS ====================
    Route::get('/transactions/{id}/edit', [ParkingController::class, 'editTransaction'])->name('transactions.edit');
    Route::put('/transactions/{id}', [ParkingController::class, 'updateTransaction'])->name('transactions.update');
    Route::delete('/transactions/{id}', [ParkingController::class, 'deleteTransaction'])->name('transactions.delete');

    // ==================== SLOT MANAGEMENT ====================
    Route::put('/slots/{id}', [ParkingController::class, 'updateSlot'])->name('slots.update');
    Route::delete('/slots/{id}', [ParkingController::class, 'deleteSlot'])->name('slots.delete');

    // ==================== API ENDPOINTS ====================
    Route::get('/api/availability', [ParkingController::class, 'availability'])->name('api.availability');
    Route::get('/api/statistics', [ParkingController::class, 'statistics'])->name('api.statistics');

    // ==================== PROFILE ROUTES ====================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== ADDITIONAL ROUTES ====================

// User View Route (alias for user-view)
Route::get('/user-view-map', [ParkingController::class, 'userView'])->name('user.view.map');

// Direct access to payment history
Route::get('/payments', [ParkingController::class, 'paymentHistory'])->name('payments');

// ==================== TEST ROUTES (Remove in production) ====================
Route::get('/test-checkout/{id}', function($id) {
    $transaction = App\Models\ParkingTransaction::find($id);
    if($transaction) {
        return response()->json([
            'message' => 'Test route works!',
            'id' => $id,
            'guest' => $transaction->guest_name,
            'plate' => $transaction->vehicle_plate,
            'status' => $transaction->status,
            'check_in' => $transaction->check_in ? $transaction->check_in->format('h:i A') : null
        ]);
    }
    return response()->json(['message' => 'Test route works! ID: ' . $id]);
});

// Debug route to check active vehicles
Route::get('/debug-active', function() {
    $active = App\Models\ParkingTransaction::with('parkingSlot')
        ->where('status', 'active')
        ->get();
    return response()->json([
        'success' => true,
        'count' => $active->count(),
        'vehicles' => $active->map(function($v) {
            return [
                'id' => $v->id,
                'guest' => $v->guest_name,
                'plate' => $v->vehicle_plate,
                'slot' => $v->parkingSlot->slot_number ?? 'N/A',
                'floor' => $v->parkingSlot->floor ?? 'N/A',
                'check_in' => $v->check_in ? $v->check_in->format('h:i A') : 'N/A'
            ];
        })
    ]);
});

// Debug route to check parking slots
Route::get('/debug-slots', function() {
    $slots = App\Models\ParkingSlot::with('activeTransaction')
        ->orderBy('floor')
        ->orderBy('slot_number')
        ->get();
    return response()->json([
        'total_slots' => $slots->count(),
        'occupied_slots' => $slots->where('status', 'occupied')->count(),
        'available_slots' => $slots->where('status', 'available')->count(),
        'slots' => $slots->map(function($s) {
            return [
                'slot' => $s->slot_number,
                'floor' => $s->floor,
                'status' => $s->status,
                'type' => $s->type,
                'vehicle' => $s->activeTransaction ? $s->activeTransaction->vehicle_plate : null
            ];
        })
    ]);
});

// Test receipt modal route
Route::get('/test-receipt', function() {
    return redirect()->route('payment.history')->with('receipt', [
        'guest_name' => 'Test User',
        'vehicle_plate' => 'TEST123',
        'user_type' => 'regular',
        'duration' => '1h 30m',
        'minutes' => 90,
        'amount' => 3.00,
        'billable_hours' => 2,
        'discount_applied' => false,
        'discount_amount' => 0,
        'check_in_time' => '10:00 AM',
        'check_out_time' => '11:30 AM',
        'slot_number' => 'B1-05',
        'floor' => 'B1'
    ]);
});
