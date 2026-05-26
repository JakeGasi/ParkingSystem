@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl shadow-xl p-6 text-white mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">MallPark Parking System</h1>
                <p class="text-blue-100 mt-1">Welcome back, {{ Auth::user()->name }}!</p>
            </div>
            <div class="text-right">
                <div class="text-sm opacity-80">{{ date('l, F j, Y') }}</div>
                <div class="text-2xl font-mono" id="liveClock">{{ date('h:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Slots</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalSlots ?? 0 }}</p>
                </div>
                <i class="fas fa-parking text-blue-500 text-3xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Occupied</p>
                    <p class="text-3xl font-bold text-red-600">{{ $occupiedSlots ?? 0 }}</p>
                </div>
                <i class="fas fa-car text-red-500 text-3xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Available</p>
                    <p class="text-3xl font-bold text-green-600">{{ $availableSlots ?? 0 }}</p>
                </div>
                <i class="fas fa-check-circle text-green-500 text-3xl"></i>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Active Vehicles</p>
                    <p class="text-3xl font-bold">{{ $activeVehicles ?? 0 }}</p>
                </div>
                <i class="fas fa-users text-white text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-2">
            <span class="font-semibold text-gray-700">Parking Capacity</span>
            <span class="text-sm font-bold {{ ($occupiedSlots ?? 0) >= 50 ? 'text-red-600' : 'text-green-600' }}">
                {{ $occupiedSlots ?? 0 }} / 50 Slots
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div class="h-4 rounded-full transition-all duration-500
                {{ ($occupiedSlots ?? 0) >= 50 ? 'bg-red-500' : 'bg-green-500' }}"
                style="width: {{ (($occupiedSlots ?? 0) / 50) * 100 }}%">
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500 text-center">
            {{ 50 - ($occupiedSlots ?? 0) }} slots remaining
        </div>
    </div>

    <!-- Full Alert -->
    @if(isset($isFull) && $isFull)
    <div class="bg-red-500 text-white rounded-xl shadow-lg p-4 mb-8 animate-pulse">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
            <div>
                <strong class="text-lg">⚠️ PARKING IS FULL!</strong>
                <p>Maximum capacity of 50 vehicles reached. No check-ins allowed.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Check-in Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">
            <i class="fas fa-sign-in-alt text-blue-500 mr-2"></i>Check-in Vehicle
        </h2>

        @if(isset($isFull) && $isFull)
        <div class="bg-red-50 border-2 border-red-300 rounded-xl p-6 text-center">
            <i class="fas fa-ban text-red-500 text-4xl mb-2"></i>
            <p class="text-red-600 font-bold">PARKING FULL - No check-ins allowed</p>
        </div>
        @else
        <form id="checkinForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <input type="text" id="guest_name" placeholder="Guest Name" class="border-2 border-gray-200 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <input type="text" id="vehicle_plate" placeholder="Vehicle Plate" class="border-2 border-gray-200 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <select id="user_type" class="border-2 border-gray-200 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="regular">Regular ($2/hour)</option>
                <option value="pwd">PWD (20% discount)</option>
                <option value="senior">Senior (20% discount)</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700 transition">
                <i class="fas fa-parking"></i> Check-in
            </button>
        </form>
        <div class="mt-3 text-sm text-gray-500">
            <i class="fas fa-info-circle"></i> <strong class="text-green-600">First 1 minute FREE</strong> | Then $2 per hour
            @if(!isset($isFull) || !$isFull)
            <span class="ml-2">Available: <strong class="text-green-600">{{ $availableSlots ?? 0 }}</strong> / 50 slots</span>
            @endif
        </div>
        @endif
    </div>

    <!-- Parking Map Preview -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4">
            <i class="fas fa-map-marked-alt text-blue-500 mr-2"></i>Parking Map Preview
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-2">
            @foreach($slots ?? [] as $slot)
            <div class="border-2 rounded-lg p-2 text-center text-xs cursor-pointer transition hover:shadow-md
                {{ $slot->status == 'occupied' ? 'bg-red-100 border-red-400' : 'bg-green-100 border-green-400' }}"
                onclick="alert('Slot: {{ $slot->slot_number }}\nFloor: {{ $slot->floor }}\nStatus: {{ ucfirst($slot->status) }}\nType: {{ ucfirst($slot->type) }}')">
                <i class="fas fa-{{ $slot->status == 'occupied' ? 'car' : 'parking' }} text-lg"></i>
                <p class="font-bold">{{ $slot->slot_number }}</p>
                <p class="text-gray-600">{{ $slot->floor }}</p>
                @if($slot->status == 'occupied' && $slot->activeTransaction)
                    <p class="text-xs text-red-600 truncate" title="{{ $slot->activeTransaction->vehicle_plate }}">
                        {{ $slot->activeTransaction->vehicle_plate }}
                    </p>
                @else
                    <p class="text-xs text-green-600 mt-1">Available</p>
                @endif
            </div>
            @endforeach
        </div>
        <div class="mt-4 text-center text-sm text-gray-500">
            <a href="{{ url('/user-overview') }}" class="text-blue-600 hover:underline mr-3">
                <i class="fas fa-eye"></i> View Full Overview
            </a>
            <a href="{{ url('/user-map') }}" class="text-blue-600 hover:underline">
                <i class="fas fa-map"></i> Find Your Vehicle
            </a>
        </div>
        <div class="mt-4 flex flex-wrap justify-center gap-4 text-xs text-gray-500">
            <span><i class="fas fa-square text-green-400"></i> Available</span>
            <span><i class="fas fa-square text-red-400"></i> Occupied</span>
            <span><i class="fas fa-square text-yellow-400"></i> PWD/Senior</span>
            <span><i class="fas fa-clock"></i> 1 min free</span>
            <span><i class="fas fa-dollar-sign"></i> $2/hour</span>
            <span><i class="fas fa-wheelchair"></i> 20% discount</span>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Live Clock
function updateClock() {
    const now = new Date();
    document.getElementById('liveClock').innerHTML = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}
setInterval(updateClock, 1000);
updateClock();

// Check-in Form
$('#checkinForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

    $.post('/checkin', {
        guest_name: $('#guest_name').val(),
        vehicle_plate: $('#vehicle_plate').val(),
        user_type: $('#user_type').val(),
        _token: '{{ csrf_token() }}'
    }, function(response) {
        if(response.success) {
            Swal.fire({
                icon: 'success',
                title: '✓ Checked In!',
                html: `<strong>${response.guest_name}</strong><br>Slot: ${response.slot} (Floor ${response.floor})<br>Remaining: ${response.remaining}/50`,
                timer: 2000,
                showConfirmButton: false
            }).then(() => location.reload());
        }
    }).fail(function(xhr) {
        Swal.fire('Error!', xhr.responseJSON?.error || 'Check-in failed', 'error');
        btn.prop('disabled', false).html('<i class="fas fa-parking"></i> Check-in');
    });
});
</script>
@endsection
