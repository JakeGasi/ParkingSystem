@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-6 text-white mb-8">
        <h1 class="text-3xl font-bold">
            <i class="fas fa-parking mr-3"></i>Parking Overview
        </h1>
        <p class="text-blue-100 mt-1">All 50 parking slots - {{ $totalSlots ?? 0 }} total slots</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <i class="fas fa-parking text-blue-500 text-3xl mb-2"></i>
            <p class="text-3xl font-bold text-blue-600">{{ $totalSlots ?? 0 }}</p>
            <p class="text-gray-500">Total Slots</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <i class="fas fa-car text-red-500 text-3xl mb-2"></i>
            <p class="text-3xl font-bold text-red-600">{{ $occupiedSlots ?? 0 }}</p>
            <p class="text-gray-500">Occupied</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
            <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
            <p class="text-3xl font-bold text-green-600">{{ $availableSlots ?? 0 }}</p>
            <p class="text-gray-500">Available</p>
        </div>
        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl shadow-lg p-6 text-white text-center">
            <i class="fas fa-percent text-3xl mb-2"></i>
            <p class="text-3xl font-bold">{{ round((($occupiedSlots ?? 0) / 50) * 100) }}%</p>
            <p class="text-sm opacity-90">Full</p>
        </div>
    </div>

    <!-- Full Alert -->
    @if(($occupiedSlots ?? 0) >= 50)
    <div class="bg-red-600 text-white rounded-xl shadow-lg p-5 mb-8 text-center animate-pulse">
        <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
        <h2 class="text-2xl font-bold">⚠️ PARKING IS FULL!</h2>
        <p>Maximum capacity of 50 vehicles reached. No available slots.</p>
    </div>
    @endif

    <!-- Progress Bar -->
    <div class="bg-white rounded-xl shadow-lg p-5 mb-8">
        <div class="flex justify-between mb-2">
            <span class="font-semibold">Parking Capacity</span>
            <span class="font-semibold">{{ $occupiedSlots ?? 0 }} / 50 Slots</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-5 overflow-hidden">
            <div class="h-5 rounded-full transition-all duration-500
                {{ ($occupiedSlots ?? 0) >= 50 ? 'bg-red-600' : 'bg-green-600' }}"
                style="width: {{ (($occupiedSlots ?? 0) / 50) * 100 }}%">
            </div>
        </div>
    </div>

    <!-- Floor Navigation -->
    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <button class="floor-tab active px-4 py-2 bg-blue-600 text-white rounded-lg" data-floor="all">
                <i class="fas fa-layer-group mr-1"></i> All Floors
            </button>
            @foreach($slotsByFloor ?? [] as $floor => $floorSlots)
            <button class="floor-tab px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" data-floor="{{ $floor }}">
                <i class="fas fa-building mr-1"></i> Floor {{ $floor }}
                <span class="ml-1 text-xs">({{ $floorSlots->where('status', 'available')->count() }}/{{ $floorSlots->count() }})</span>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Parking Map by Floor -->
    @forelse($slotsByFloor ?? [] as $floor => $floorSlots)
    <div class="floor-section bg-white rounded-2xl shadow-xl overflow-hidden mb-8" data-floor="{{ $floor }}">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-building mr-2"></i> Floor {{ $floor }}
                </h2>
                <div class="text-white text-sm">
                    <span class="text-green-400">{{ $floorSlots->where('status', 'available')->count() }} available</span>
                    <span class="text-red-400 ml-2">{{ $floorSlots->where('status', 'occupied')->count() }} occupied</span>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                @foreach($floorSlots as $slot)
                <div class="border-2 rounded-xl p-3 text-center transition-all duration-300
                    {{ $slot->status == 'occupied' ? 'bg-red-100 border-red-500' : 'bg-green-100 border-green-500' }}">
                    <i class="fas fa-{{ $slot->status == 'occupied' ? 'car' : 'parking' }} text-2xl mb-1"></i>
                    <p class="font-bold text-sm">{{ $slot->slot_number }}</p>
                    @if($slot->type == 'pwd')
                        <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded-full text-xs">
                            <i class="fas fa-wheelchair"></i> PWD
                        </span>
                    @endif
                    @if($slot->status == 'occupied' && $slot->activeTransaction)
                        <p class="text-xs font-mono mt-1 text-red-600 truncate">{{ $slot->activeTransaction->vehicle_plate }}</p>
                    @else
                        <p class="text-xs text-green-600 mt-1">Available</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @empty
    <div class="bg-yellow-100 rounded-2xl shadow-xl p-12 text-center">
        <i class="fas fa-database text-yellow-500 text-5xl mb-3"></i>
        <p class="text-gray-700 text-lg">No parking slots found</p>
        <p class="text-gray-500 text-sm">Please run: php artisan db:seed --class=ParkingSlotSeeder</p>
    </div>
    @endforelse

    <!-- Legend -->
    <div class="bg-white rounded-xl shadow-lg p-4 mt-4">
        <div class="flex flex-wrap justify-center gap-6 text-sm">
            <div class="flex items-center">
                <div class="w-5 h-5 bg-green-500 rounded-full mr-2"></div>
                <span>Available Slot</span>
            </div>
            <div class="flex items-center">
                <div class="w-5 h-5 bg-red-500 rounded-full mr-2"></div>
                <span>Occupied Slot</span>
            </div>
            <div class="flex items-center">
                <div class="w-5 h-5 bg-yellow-500 rounded-full mr-2"></div>
                <span>PWD/Senior Priority</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-wheelchair text-purple-600 mr-2"></i>
                <span>20% Discount</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-hourglass-half text-orange-500 mr-2"></i>
                <span>First 1 min Free</span>
            </div>
        </div>
    </div>
</div>

<script>
// Floor tabs
document.querySelectorAll('.floor-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const floor = this.dataset.floor;
        document.querySelectorAll('.floor-tab').forEach(t => {
            t.classList.remove('active', 'bg-blue-600', 'text-white');
            t.classList.add('bg-gray-200', 'text-gray-700');
        });
        this.classList.add('active', 'bg-blue-600', 'text-white');

        document.querySelectorAll('.floor-section').forEach(section => {
            if (floor === 'all' || section.dataset.floor === floor) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    });
});
</script>
@endsection
