<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use App\Models\ParkingTransaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;

        $activeVehicles = ParkingTransaction::where('status', 'active')->count();
        $todayCheckouts = ParkingTransaction::whereDate('check_out', today())->count();
        $todayRevenue = ParkingTransaction::whereDate('check_out', today())->sum('amount');

        $recentTransactions = ParkingTransaction::with('parkingSlot')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $slots = ParkingSlot::with('activeTransaction')->get();

        $isFull = $availableSlots == 0;

        return view('dashboard', compact(
            'totalSlots',
            'occupiedSlots',
            'availableSlots',
            'activeVehicles',
            'todayCheckouts',
            'todayRevenue',
            'recentTransactions',
            'slots',
            'isFull'
        ));
    }

    public function getStats()
    {
        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;

        return response()->json([
            'total' => $totalSlots,
            'occupied' => $occupiedSlots,
            'available' => $availableSlots,
            'is_full' => $availableSlots == 0
        ]);
    }
}
