<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingSlot;

class ParkingSlotSeeder extends Seeder
{
    public function run()
    {
        // Create 50 parking slots (5 floors x 10 slots)
        $floors = ['B1', 'B2', 'B3', 'B4', 'L1'];
        $slotCount = 1;

        foreach ($floors as $floor) {
            for ($i = 1; $i <= 10; $i++) {
                $type = 'regular';

                // Make slots 3, 6, 9 on each floor as PWD/Senior priority
                if (in_array($i, [3, 6, 9])) {
                    $type = 'pwd';
                }

                ParkingSlot::create([
                    'slot_number' => $floor . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'floor' => $floor,
                    'type' => $type,
                    'status' => 'available'
                ]);
            }
        }

        // Also add L2 floor with 10 slots to reach 50
        for ($i = 1; $i <= 10; $i++) {
            $type = 'regular';
            if (in_array($i, [3, 6, 9])) {
                $type = 'pwd';
            }

            ParkingSlot::create([
                'slot_number' => 'L2-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'floor' => 'L2',
                'type' => $type,
                'status' => 'available'
            ]);
        }
    }
}
