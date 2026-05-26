<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingSlot;

class ParkingSlotSeeder extends Seeder
{
    public function run()
    {
        // Create 50 parking slots (5 floors x 10 slots = 50)
        $floors = ['B1', 'B2', 'B3', 'B4', 'L1'];

        foreach ($floors as $floor) {
            for ($i = 1; $i <= 10; $i++) {
                $type = 'regular';
                // Priority slots for PWD/Senior on positions 3, 6, 9
                if (in_array($i, [3, 6, 9])) {
                    $type = 'pwd';
                }

                ParkingSlot::updateOrCreate(
                    ['slot_number' => $floor . '-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                    [
                        'floor' => $floor,
                        'type' => $type,
                        'status' => 'available'
                    ]
                );
            }
        }

        $this->command->info('✅ 50 parking slots created successfully!');
    }
}
