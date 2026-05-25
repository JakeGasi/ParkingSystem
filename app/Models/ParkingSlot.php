<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    protected $fillable = ['slot_number', 'floor', 'type', 'status'];

    public function activeTransaction()
    {
        return $this->hasOne(ParkingTransaction::class)->where('status', 'active');
    }
}
