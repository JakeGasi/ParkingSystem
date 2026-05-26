<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;
use App\Models\ParkingTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    // ==================== USER MAP ====================
    public function userMap()
    {
        $slots = ParkingSlot::with('activeTransaction')
            ->orderBy('floor')
            ->orderBy('slot_number')
            ->get();

        $slotsByFloor = $slots->groupBy('floor');
        $activeVehicles = ParkingTransaction::with('parkingSlot')->where('status', 'active')->get();
        $totalSlots = 50;
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;
        $isFull = $occupiedSlots >= 50;

        // Add PWD and Senior counts
        $pwdCount = ParkingTransaction::where('status', 'active')->where('user_type', 'pwd')->count();
        $seniorCount = ParkingTransaction::where('status', 'active')->where('user_type', 'senior')->count();

        return view('user-map', compact('slots', 'slotsByFloor', 'activeVehicles',
            'totalSlots', 'occupiedSlots', 'availableSlots', 'isFull', 'pwdCount', 'seniorCount'));
    }

    // ==================== USER VIEW - SHOW WHERE VEHICLES ARE PARKED ====================
    public function userView()
    {
        $slots = ParkingSlot::with('activeTransaction')
            ->orderBy('floor')
            ->orderBy('slot_number')
            ->get();

        // Group slots by floor for organized display
        $slotsByFloor = $slots->groupBy('floor');

        $activeVehicles = ParkingTransaction::with('parkingSlot')
            ->where('status', 'active')
            ->get();

        $totalSlots = 50;
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;
        $isFull = $occupiedSlots >= 50;

        // Get PWD and Senior counts
        $pwdCount = ParkingTransaction::where('status', 'active')->where('user_type', 'pwd')->count();
        $seniorCount = ParkingTransaction::where('status', 'active')->where('user_type', 'senior')->count();

        return view('user-view', compact('slots', 'slotsByFloor', 'activeVehicles',
            'totalSlots', 'occupiedSlots', 'availableSlots', 'isFull', 'pwdCount', 'seniorCount'));
    }

    // ==================== USER OVERVIEW - 50 SLOTS WITH FLOOR VIEW ====================
    public function userOverview()
    {
        $slots = ParkingSlot::with('activeTransaction')
            ->orderBy('floor')
            ->orderBy('slot_number')
            ->get();

        $slotsByFloor = $slots->groupBy('floor');
        $activeVehicles = ParkingTransaction::with('parkingSlot')
            ->where('status', 'active')
            ->get();

        $totalSlots = 50;
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;
        $isFull = $occupiedSlots >= 50;

        $pwdCount = ParkingTransaction::where('status', 'active')->where('user_type', 'pwd')->count();
        $seniorCount = ParkingTransaction::where('status', 'active')->where('user_type', 'senior')->count();

        return view('user-overview', compact('slots', 'slotsByFloor', 'activeVehicles',
            'totalSlots', 'occupiedSlots', 'availableSlots', 'isFull', 'pwdCount', 'seniorCount'));
    }

    // ==================== ACTIVE VEHICLES ====================
    public function activeVehicles()
    {
        $activeVehicles = ParkingTransaction::with('parkingSlot')
            ->where('status', 'active')
            ->orderBy('check_in', 'desc')
            ->get();

        return view('active-vehicles', compact('activeVehicles'));
    }

    // ==================== PAYMENT HISTORY (IMPROVED) ====================
    public function paymentHistory()
    {
        // Get all completed transactions with proper ordering
        $completedTransactions = ParkingTransaction::with('parkingSlot')
            ->where('status', 'completed')
            ->orderBy('check_out', 'desc')
            ->paginate(20);

        // Get active transactions for real-time duration display
        $activeTransactions = ParkingTransaction::with('parkingSlot')
            ->where('status', 'active')
            ->orderBy('check_in', 'desc')
            ->get();

        $totalRevenue = ParkingTransaction::where('status', 'completed')->sum('amount');
        $todayRevenue = ParkingTransaction::whereDate('check_out', today())->sum('amount');
        $totalTransactions = ParkingTransaction::where('status', 'completed')->count();

        // Calculate average revenue per transaction
        $averageRevenue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        return view('payment-history', compact(
            'completedTransactions',
            'activeTransactions',
            'totalRevenue',
            'todayRevenue',
            'totalTransactions',
            'averageRevenue'
        ));
    }

    // ==================== TRANSACTIONS (CRUD) ====================
    public function transactions()
    {
        $transactions = ParkingTransaction::with('parkingSlot')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('transactions', compact('transactions'));
    }

    // EDIT TRANSACTION - Show edit form
    public function editTransaction($id)
    {
        $transaction = ParkingTransaction::findOrFail($id);
        $slots = ParkingSlot::all();

        return view('edit-transaction', compact('transaction', 'slots'));
    }

    // UPDATE TRANSACTION - Save changes
    public function updateTransaction(Request $request, $id)
    {
        $transaction = ParkingTransaction::findOrFail($id);

        $request->validate([
            'guest_name' => 'required|string|max:100',
            'vehicle_plate' => 'required|string|max:20',
            'user_type' => 'required|in:regular,pwd,senior',
        ]);

        $transaction->update([
            'guest_name' => $request->guest_name,
            'vehicle_plate' => strtoupper($request->vehicle_plate),
            'user_type' => $request->user_type,
        ]);

        return redirect('/transactions')->with('success', 'Transaction updated successfully!');
    }

    // DELETE TRANSACTION
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

        return response()->json(['success' => true, 'message' => 'Transaction deleted!']);
    }

    // ==================== SLOT MANAGEMENT ====================
    public function slots()
    {
        $slots = ParkingSlot::orderBy('floor')->orderBy('slot_number')->get();
        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('status', 'occupied')->count();
        $availableSlots = $totalSlots - $occupiedSlots;

        return view('slots', compact('slots', 'totalSlots', 'occupiedSlots', 'availableSlots'));
    }

    public function updateSlot(Request $request, $id)
    {
        $slot = ParkingSlot::findOrFail($id);
        $slot->type = $request->type;
        $slot->save();

        return response()->json(['success' => true]);
    }

    public function deleteSlot($id)
    {
        $slot = ParkingSlot::findOrFail($id);
        if ($slot->status === 'occupied') {
            return response()->json(['error' => 'Cannot delete occupied slot'], 400);
        }
        $slot->delete();
        return response()->json(['success' => true]);
    }

    // ==================== CHECK-IN ====================
    public function checkIn(Request $request)
    {
        $request->validate([
            'guest_name' => 'required|string|max:100',
            'vehicle_plate' => 'required|string|max:20',
            'user_type' => 'required|in:regular,pwd,senior',
        ]);

        $occupiedCount = ParkingSlot::where('status', 'occupied')->count();

        if ($occupiedCount >= 50) {
            return response()->json(['error' => '⚠️ PARKING IS FULL!'], 400);
        }

        $slot = ParkingSlot::where('status', 'available')->first();

        if (!$slot) {
            return response()->json(['error' => 'No available slots.'], 400);
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
            'slot' => $slot->slot_number,
            'floor' => $slot->floor,
            'remaining' => $remaining
        ]);
    }

    // ==================== CHECK-OUT (UPDATED - 1 minute free) ====================
    public function checkOut($id)
    {
        try {
            $transaction = ParkingTransaction::find($id);

            if (!$transaction) {
                return redirect()->back()->with('error', 'Transaction not found');
            }

            if ($transaction->status === 'completed') {
                return redirect()->back()->with('error', 'Vehicle already checked out');
            }

            $checkoutTime = Carbon::now();
            $checkinTime = Carbon::parse($transaction->check_in);
            $minutes = $checkinTime->diffInMinutes($checkoutTime);

            $amount = 0;
            $billableHours = 0;
            $discount = 0;
            $discountApplied = false;

            // CHANGED: First 1 minute is free (was 30 minutes)
            if ($minutes > 1) {
                $billableHours = ceil(($minutes - 1) / 60);
                $amount = $billableHours * 2;
                if (in_array($transaction->user_type, ['pwd', 'senior'])) {
                    $discount = $amount * 0.2;
                    $amount = $amount - $discount;
                    $discountApplied = true;
                }
                $amount = round($amount, 2);
            }

            $transaction->check_out = $checkoutTime;
            $transaction->amount = $amount;
            $transaction->status = 'completed';
            $transaction->save();

            $slot = ParkingSlot::find($transaction->parking_slot_id);
            if ($slot) {
                $slot->status = 'available';
                $slot->save();
            }

            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            $duration = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';

            // Store receipt data for modal display
            $receiptData = [
                'guest_name' => $transaction->guest_name,
                'vehicle_plate' => $transaction->vehicle_plate,
                'user_type' => $transaction->user_type,
                'duration' => $duration,
                'minutes' => $minutes,
                'amount' => $amount,
                'billable_hours' => $billableHours,
                'discount_applied' => $discountApplied,
                'discount_amount' => round($discount, 2),
                'check_in_time' => $checkinTime->format('h:i A'),
                'check_out_time' => $checkoutTime->format('h:i A'),
                'slot_number' => $slot ? $slot->slot_number : 'N/A',
                'floor' => $slot ? $slot->floor : 'N/A',
                'free_minutes' => 1
            ];

            // Redirect to payment history with success and receipt data
            return redirect()->route('payment.history')
                ->with('checkout_success', true)
                ->with('receipt', $receiptData);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }

    // ==================== SEARCH VEHICLE ====================
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
            return response()->json([
                'success' => true,
                'found' => true,
                'guest_name' => $vehicle->guest_name,
                'vehicle_plate' => $vehicle->vehicle_plate,
                'slot_number' => $vehicle->parkingSlot->slot_number,
                'floor' => $vehicle->parkingSlot->floor,
                'check_in_time' => $vehicle->check_in ? $vehicle->check_in->format('h:i A') : 'N/A',
                'user_type' => $vehicle->user_type
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => false,
            'message' => 'Vehicle not found'
        ]);
    }

    // ==================== API ====================
    public function availability()
    {
        return response()->json([
            'total_slots' => ParkingSlot::count(),
            'occupied_slots' => ParkingSlot::where('status', 'occupied')->count(),
            'available_slots' => ParkingSlot::where('status', 'available')->count(),
            'is_full' => ParkingSlot::where('status', 'occupied')->count() >= 50
        ]);
    }
}
