<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use App\Models\ParkingTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;
        $activeVehicles = ParkingTransaction::where('status', 'active')->count();
        $todayRevenue = ParkingTransaction::whereDate('check_out', today())->sum('amount');

        $slots = ParkingSlot::with('activeTransaction')
            ->orderBy('floor')
            ->orderBy('slot_number')
            ->get();

        $isFull = $occupiedSlots >= 50;

        return view('dashboard', compact(
            'totalSlots', 'occupiedSlots', 'availableSlots',
            'activeVehicles', 'todayRevenue', 'slots', 'isFull'
        ));
    }
}
