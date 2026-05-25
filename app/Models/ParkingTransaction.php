<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingTransaction extends Model
{
    protected $fillable = ['guest_name', 'vehicle_plate', 'user_type', 'parking_slot_id', 'check_in', 'check_out', 'amount', 'status'];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function parkingSlot()
    {
        return $this->belongsTo(ParkingSlot::class);
    }

    public function calculatePayment()
    {
        if (!$this->check_out) return 0;

        $minutes = $this->check_in->diffInMinutes($this->check_out);

        // First 30 minutes free
        if ($minutes <= 30) return 0;

        $billableHours = ceil(($minutes - 30) / 60);
        $amount = $billableHours * 2; // $2 per hour

        // 20% discount for PWD and Senior
        if (in_array($this->user_type, ['pwd', 'senior'])) {
            $amount = $amount * 0.8;
        }

        return round($amount, 2);
    }
}
