<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use App\Models\ParkingTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParkingController extends Controller
{
    // ==================== DASHBOARD ====================
    public function dashboard()
    {
        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;
        $activeVehicles = ParkingTransaction::where('status', 'active')->count();
        $todayRevenue = ParkingTransaction::whereDate('check_out', today())->sum('amount');

        // PWD and Senior Citizen Statistics
        $pwdCount = ParkingTransaction::where('status', 'active')->where('user_type', 'pwd')->count();
        $seniorCount = ParkingTransaction::where('status', 'active')->where('user_type', 'senior')->count();
        $regularCount = ParkingTransaction::where('status', 'active')->where('user_type', 'regular')->count();

        // Priority slots status
        $prioritySlotsTotal = ParkingSlot::whereIn('type', ['pwd', 'senior'])->count();
        $prioritySlotsOccupied = ParkingSlot::whereIn('type', ['pwd', 'senior'])->where('status', 'occupied')->count();
        $prioritySlotsAvailable = $prioritySlotsTotal - $prioritySlotsOccupied;

        $slots = ParkingSlot::with('activeTransaction')
            ->orderBy('floor')
            ->orderBy('slot_number')
            ->get();

        // Group slots by floor for map view
        $slotsByFloor = $slots->groupBy('floor');

        $isFull = $occupiedSlots >= 50;

        return view('dashboard', compact(
            'totalSlots', 'occupiedSlots', 'availableSlots',
            'activeVehicles', 'todayRevenue', 'slots', 'isFull',
            'pwdCount', 'seniorCount', 'regularCount',
            'prioritySlotsTotal', 'prioritySlotsOccupied', 'prioritySlotsAvailable',
            'slotsByFloor'
        ));
    }

    // ==================== USER MAP - FIND YOUR VEHICLE ====================
    public function userMap()
    {
        $slots = ParkingSlot::with('activeTransaction')
            ->orderBy('floor')
            ->orderBy('slot_number')
            ->get();

        // Group slots by floor for better organization
        $slotsByFloor = $slots->groupBy('floor');

        $activeVehicles = ParkingTransaction::with('parkingSlot')
            ->where('status', 'active')
            ->get();

        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;

        // PWD and Senior Statistics
        $pwdCount = ParkingTransaction::where('status', 'active')->where('user_type', 'pwd')->count();
        $seniorCount = ParkingTransaction::where('status', 'active')->where('user_type', 'senior')->count();

        return view('user-map', compact('slots', 'slotsByFloor', 'activeVehicles',
            'totalSlots', 'occupiedSlots', 'availableSlots', 'pwdCount', 'seniorCount'));
    }

    // ==================== SEARCH VEHICLE BY PLATE ====================
    public function searchVehicle(Request $request)
    {
        $plate = strtoupper($request->get('plate'));

        if (empty($plate)) {
            return response()->json(['error' => 'Please enter a plate number'], 400);
        }

        $vehicle = ParkingTransaction::with('parkingSlot')
            ->where('vehicle_plate', 'LIKE', "%{$plate}%")
            ->where('status', 'active')
            ->first();

        if ($vehicle) {
            $checkinTime = Carbon::parse($vehicle->check_in);
            $minutes = $checkinTime->diffInMinutes(Carbon::now());
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;

            return response()->json([
                'success' => true,
                'found' => true,
                'guest_name' => $vehicle->guest_name,
                'vehicle_plate' => $vehicle->vehicle_plate,
                'slot_number' => $vehicle->parkingSlot->slot_number,
                'floor' => $vehicle->parkingSlot->floor,
                'check_in_time' => $checkinTime->format('h:i A'),
                'duration' => ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm',
                'user_type' => $vehicle->user_type
            ]);
        } else {
            return response()->json([
                'success' => true,
                'found' => false,
                'message' => 'Vehicle not found in parking area'
            ]);
        }
    }

    // ==================== ACTIVE VEHICLES ====================
    public function activeVehicles()
    {
        $activeVehicles = ParkingTransaction::with('parkingSlot')
            ->where('status', 'active')
            ->orderBy('check_in', 'desc')
            ->get();

        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $pwdCount = ParkingTransaction::where('status', 'active')->where('user_type', 'pwd')->count();
        $seniorCount = ParkingTransaction::where('status', 'active')->where('user_type', 'senior')->count();

        return view('active-vehicles', compact('activeVehicles', 'totalSlots', 'occupiedSlots', 'pwdCount', 'seniorCount'));
    }

    // ==================== PAYMENT HISTORY ====================
    public function paymentHistory()
    {
        $completedTransactions = ParkingTransaction::with('parkingSlot')
            ->where('status', 'completed')
            ->orderBy('check_out', 'desc')
            ->paginate(20);

        $totalRevenue = ParkingTransaction::where('status', 'completed')->sum('amount');
        $todayRevenue = ParkingTransaction::whereDate('check_out', today())->sum('amount');

        return view('payment-history', compact('completedTransactions', 'totalRevenue', 'todayRevenue'));
    }

    // ==================== TRANSACTIONS ====================
    public function transactions()
    {
        $transactions = ParkingTransaction::with('parkingSlot')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalRevenue = ParkingTransaction::where('status', 'completed')->sum('amount');
        $totalTransactions = ParkingTransaction::count();
        $activeCount = ParkingTransaction::where('status', 'active')->count();
        $completedCount = ParkingTransaction::where('status', 'completed')->count();

        return view('transactions', compact('transactions', 'totalRevenue', 'totalTransactions', 'activeCount', 'completedCount'));
    }

    // ==================== CHECK-IN ====================
    public function checkIn(Request $request)
    {
        try {
            $request->validate([
                'guest_name' => 'required|string|max:100',
                'vehicle_plate' => 'required|string|max:20',
                'user_type' => 'required|in:regular,pwd,senior',
            ]);

            $occupiedCount = ParkingSlot::where('status', 'occupied')->count();

            if ($occupiedCount >= 50) {
                return response()->json(['error' => '⚠️ PARKING IS FULL! Maximum 50 vehicles reached.'], 400);
            }

            // Check if vehicle already parked
            $existingVehicle = ParkingTransaction::where('vehicle_plate', strtoupper($request->vehicle_plate))
                ->where('status', 'active')
                ->first();

            if ($existingVehicle) {
                return response()->json(['error' => 'This vehicle is already parked in the mall!'], 400);
            }

            // Find available slot (priority for PWD/Senior)
            $slot = null;

            if (in_array($request->user_type, ['pwd', 'senior'])) {
                $slot = ParkingSlot::where('status', 'available')
                    ->whereIn('type', ['pwd', 'senior'])
                    ->first();
                if (!$slot) $slot = ParkingSlot::where('status', 'available')->first();
            } else {
                $slot = ParkingSlot::where('status', 'available')
                    ->where('type', 'regular')
                    ->first();
                if (!$slot) $slot = ParkingSlot::where('status', 'available')->first();
            }

            if (!$slot) {
                return response()->json(['error' => 'No available slots at the moment.'], 400);
            }

            $transaction = ParkingTransaction::create([
                'guest_name' => $request->guest_name,
                'vehicle_plate' => strtoupper($request->vehicle_plate),
                'user_type' => $request->user_type,
                'parking_slot_id' => $slot->id,
                'check_in' => Carbon::now(),
                'status' => 'active',
                'amount' => 0
            ]);

            $slot->status = 'occupied';
            $slot->save();

            $remaining = 50 - ParkingSlot::where('status', 'occupied')->count();

            return response()->json([
                'success' => true,
                'message' => '✓ Check-in successful!',
                'slot' => $slot->slot_number,
                'floor' => $slot->floor,
                'remaining' => $remaining,
                'is_full' => $remaining == 0,
                'transaction_id' => $transaction->id
            ]);

        } catch (\Exception $e) {
            Log::error('Check-in error: ' . $e->getMessage());
            return response()->json(['error' => 'Check-in failed: ' . $e->getMessage()], 500);
        }
    }

    // ==================== CHECK-OUT WITH DETAILED RECEIPT ====================
    public function checkOut($id)
    {
        try {
            Log::info('Checkout attempt for transaction ID: ' . $id);

            $transaction = ParkingTransaction::find($id);

            if (!$transaction) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            if ($transaction->status === 'completed') {
                return response()->json(['error' => 'Vehicle already checked out'], 400);
            }

            $checkoutTime = Carbon::now();
            $checkinTime = Carbon::parse($transaction->check_in);
            $minutes = $checkinTime->diffInMinutes($checkoutTime);

            // Calculate amount
            $amount = 0;
            $billableHours = 0;
            $discount = 0;
            $discountApplied = false;

            if ($minutes > 30) {
                $billableHours = ceil(($minutes - 30) / 60);
                $amount = $billableHours * 2;

                if (in_array($transaction->user_type, ['pwd', 'senior'])) {
                    $discount = $amount * 0.2;
                    $amount = $amount - $discount;
                    $discountApplied = true;
                }
                $amount = round($amount, 2);
            }

            // Update transaction
            $transaction->check_out = $checkoutTime;
            $transaction->amount = $amount;
            $transaction->status = 'completed';
            $transaction->save();

            // Free the parking slot
            $slot = ParkingSlot::find($transaction->parking_slot_id);
            if ($slot) {
                $slot->status = 'available';
                $slot->save();
                Log::info('Slot freed: ' . $slot->slot_number);
            }

            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            $duration = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';

            Log::info('Checkout successful for: ' . $transaction->guest_name . ' - Amount: $' . $amount);

            return response()->json([
                'success' => true,
                'message' => 'Checkout successful!',
                'amount' => $amount,
                'guest_name' => $transaction->guest_name,
                'vehicle_plate' => $transaction->vehicle_plate,
                'slot_number' => $slot ? $slot->slot_number : 'N/A',
                'floor' => $slot ? $slot->floor : 'N/A',
                'duration' => $duration,
                'minutes' => $minutes,
                'billable_hours' => $billableHours,
                'rate_per_hour' => 2,
                'free_minutes' => 30,
                'discount_applied' => $discountApplied,
                'discount_amount' => round($discount, 2),
                'check_in_time' => $checkinTime->format('h:i A'),
                'check_out_time' => $checkoutTime->format('h:i A'),
                'user_type' => $transaction->user_type
            ]);

        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return response()->json(['error' => 'Checkout failed: ' . $e->getMessage()], 500);
        }
    }

    // ==================== EDIT TRANSACTION ====================
    public function editTransaction($id)
    {
        $transaction = ParkingTransaction::findOrFail($id);
        $slots = ParkingSlot::all();

        return view('edit-transaction', compact('transaction', 'slots'));
    }

    // ==================== UPDATE TRANSACTION ====================
    public function updateTransaction(Request $request, $id)
    {
        $transaction = ParkingTransaction::findOrFail($id);

        $request->validate([
            'guest_name' => 'required|string|max:100',
            'vehicle_plate' => 'required|string|max:20',
            'user_type' => 'required|in:regular,pwd,senior',
            'status' => 'required|in:active,completed',
        ]);

        $oldStatus = $transaction->status;
        $newStatus = $request->status;

        $transaction->guest_name = $request->guest_name;
        $transaction->vehicle_plate = strtoupper($request->vehicle_plate);
        $transaction->user_type = $request->user_type;
        $transaction->status = $newStatus;

        // If marking as completed and no checkout time, set it
        if ($newStatus == 'completed' && $oldStatus == 'active' && !$transaction->check_out) {
            $transaction->check_out = Carbon::now();

            // Calculate amount
            $minutes = Carbon::parse($transaction->check_in)->diffInMinutes($transaction->check_out);
            $amount = 0;
            if ($minutes > 30) {
                $billableHours = ceil(($minutes - 30) / 60);
                $amount = $billableHours * 2;
                if (in_array($transaction->user_type, ['pwd', 'senior'])) {
                    $amount = $amount * 0.8;
                }
                $amount = round($amount, 2);
            }
            $transaction->amount = $amount;

            // Free the slot
            $slot = ParkingSlot::find($transaction->parking_slot_id);
            if ($slot) {
                $slot->status = 'available';
                $slot->save();
            }
        }

        $transaction->save();

        return redirect()->route('transactions')->with('success', 'Transaction updated successfully!');
    }

    // ==================== DELETE TRANSACTION ====================
    public function deleteTransaction($id)
    {
        $transaction = ParkingTransaction::findOrFail($id);

        // If transaction is active, free the slot first
        if ($transaction->status === 'active') {
            $slot = ParkingSlot::find($transaction->parking_slot_id);
            if ($slot) {
                $slot->status = 'available';
                $slot->save();
            }
        }

        $transaction->delete();

        return response()->json(['success' => true, 'message' => 'Transaction deleted successfully!']);
    }

    // ==================== SLOT MANAGEMENT ====================
    public function slots()
    {
        $slots = ParkingSlot::orderBy('floor')
            ->orderBy('slot_number')
            ->get();

        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;
        $prioritySlots = ParkingSlot::whereIn('type', ['pwd', 'senior'])->count();

        return view('slots', compact('slots', 'totalSlots', 'occupiedSlots', 'availableSlots', 'prioritySlots'));
    }

    public function updateSlot(Request $request, $id)
    {
        $slot = ParkingSlot::findOrFail($id);

        $request->validate([
            'type' => 'required|in:regular,pwd,senior',
        ]);

        $slot->type = $request->type;
        $slot->save();

        return response()->json(['success' => true, 'message' => 'Slot type updated!']);
    }

    public function deleteSlot($id)
    {
        $slot = ParkingSlot::findOrFail($id);

        if ($slot->status === 'occupied') {
            return response()->json(['error' => 'Cannot delete an occupied slot'], 400);
        }

        $slot->delete();

        return response()->json(['success' => true, 'message' => 'Slot deleted successfully!']);
    }

    // ==================== API ENDPOINTS ====================
    public function availability()
    {
        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;

        // Get active transactions for real-time updates
        $activeTransactions = ParkingTransaction::with('parkingSlot')
            ->where('status', 'active')
            ->get()
            ->map(function($transaction) {
                $checkinTime = Carbon::parse($transaction->check_in);
                $minutes = $checkinTime->diffInMinutes(Carbon::now());
                $hours = floor($minutes / 60);
                $mins = $minutes % 60;

                return [
                    'id' => $transaction->id,
                    'guest_name' => $transaction->guest_name,
                    'vehicle_plate' => $transaction->vehicle_plate,
                    'user_type' => $transaction->user_type,
                    'slot_number' => $transaction->parkingSlot->slot_number ?? 'N/A',
                    'floor' => $transaction->parkingSlot->floor ?? 'N/A',
                    'check_in_time' => $checkinTime->format('h:i A'),
                    'duration' => ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm'
                ];
            });

        return response()->json([
            'success' => true,
            'total_slots' => $totalSlots,
            'occupied_slots' => $occupiedSlots,
            'available_slots' => $availableSlots,
            'is_full' => $occupiedSlots >= $totalSlots,
            'percentage_full' => round(($occupiedSlots / $totalSlots) * 100, 2),
            'active_vehicles_count' => ParkingTransaction::where('status', 'active')->count(),
            'today_revenue' => ParkingTransaction::whereDate('check_out', today())->sum('amount'),
            'active_transactions' => $activeTransactions
        ]);
    }

    // ==================== STATISTICS ====================
    public function statistics()
    {
        $totalTransactions = ParkingTransaction::count();
        $totalRevenue = ParkingTransaction::where('status', 'completed')->sum('amount');
        $averageAmount = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Daily stats for last 7 days
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyStats[] = [
                'date' => $date->format('M d'),
                'revenue' => ParkingTransaction::whereDate('check_out', $date)->sum('amount'),
                'count' => ParkingTransaction::whereDate('check_out', $date)->count()
            ];
        }

        // Vehicle type distribution
        $vehicleTypes = [
            'regular' => ParkingTransaction::count(),
            'pwd' => ParkingTransaction::where('user_type', 'pwd')->count(),
            'senior' => ParkingTransaction::where('user_type', 'senior')->count()
        ];

        // Hourly distribution
        $hourlyStats = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyStats[] = [
                'hour' => $i,
                'count' => ParkingTransaction::whereTime('check_in', '>=', sprintf('%02d:00:00', $i))
                    ->whereTime('check_in', '<', sprintf('%02d:00:00', $i + 1))
                    ->count()
            ];
        }

        return response()->json([
            'success' => true,
            'total_transactions' => $totalTransactions,
            'total_revenue' => $totalRevenue,
            'average_amount' => round($averageAmount, 2),
            'daily_stats' => $dailyStats,
            'vehicle_types' => $vehicleTypes,
            'hourly_stats' => $hourlyStats
        ]);
    }
}
