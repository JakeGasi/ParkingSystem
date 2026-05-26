@extends('layouts.app')

@section('content')
<style>
    .slot-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .slot-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
    }
    .slot-highlight {
        animation: pulse 0.5s ease-in-out 3;
        box-shadow: 0 0 20px rgba(59,130,246,0.5);
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .floor-tab {
        transition: all 0.3s ease;
    }
    .floor-tab.active {
        background-color: #3b82f6;
        color: white;
    }
    .full-alert {
        animation: blink 1s infinite;
        background-color: #dc2626;
    }
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.8; }
        100% { opacity: 1; }
    }
</style>

<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-teal-700 rounded-2xl shadow-xl p-6 text-white mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">
                    <i class="fas fa-map-marked-alt mr-3"></i>Find Your Vehicle
                </h1>
                <p class="text-green-100 mt-1">Enter your plate number or browse the map to locate your vehicle</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">{{ $activeVehicles->count() }}</div>
                <div class="text-sm opacity-90">Vehicles Parked</div>
                <div class="text-xs opacity-75">{{ $availableSlots }} of 50 available</div>
            </div>
        </div>
    </div>

    <!-- FULL PARKING ALERT (RED when 50/50) -->
    @if($isFull)
    <div class="bg-red-600 text-white rounded-xl shadow-lg p-4 mb-6 full-alert">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-3xl mr-4"></i>
            <div>
                <strong class="text-xl">⚠️ PARKING IS FULL!</strong>
                <p>Maximum capacity of 50 vehicles reached. No available slots.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Search Section -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-search text-blue-500 mr-2"></i>Search Your Vehicle
        </h2>
        <div class="flex flex-col md:flex-row gap-4">
            <input type="text" id="searchPlate" placeholder="Enter your vehicle plate number (e.g., ABC-1234)"
                   class="flex-1 border-2 border-gray-200 rounded-xl p-4 text-lg focus:outline-none focus:border-blue-500">
            <button onclick="findVehicle()" class="bg-blue-600 text-white px-8 py-4 rounded-xl font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-location-dot mr-2"></i>Find My Vehicle
            </button>
        </div>
        <div id="searchResult" class="mt-4 hidden"></div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow p-4 text-white text-center">
            <i class="fas fa-parking text-3xl mb-2"></i>
            <p class="text-3xl font-bold">{{ $totalSlots }}</p>
            <p class="text-sm">Total Slots</p>
        </div>
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow p-4 text-white text-center">
            <i class="fas fa-car text-3xl mb-2"></i>
            <p class="text-3xl font-bold">{{ $occupiedSlots }}</p>
            <p class="text-sm">Occupied</p>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow p-4 text-white text-center">
            <i class="fas fa-check-circle text-3xl mb-2"></i>
            <p class="text-3xl font-bold">{{ $availableSlots }}</p>
            <p class="text-sm">Available</p>
        </div>
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow p-4 text-white text-center">
            <i class="fas fa-users text-3xl mb-2"></i>
            <p class="text-3xl font-bold">{{ $activeVehicles->count() }}</p>
            <p class="text-sm">Active Vehicles</p>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white rounded-xl shadow p-4 mb-6">
        <div class="flex justify-between mb-2">
            <span class="font-semibold">Parking Capacity</span>
            <span class="font-semibold {{ $occupiedSlots >= 50 ? 'text-red-600' : 'text-green-600' }}">
                {{ $occupiedSlots }} / 50 Slots
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div class="h-4 rounded-full transition-all duration-500
                {{ $occupiedSlots >= 50 ? 'bg-red-600' : ($occupiedSlots >= 40 ? 'bg-yellow-500' : 'bg-green-500') }}"
                style="width: {{ ($occupiedSlots / 50) * 100 }}%">
            </div>
        </div>
    </div>

    <!-- Floor Tabs Navigation -->
    <div class="bg-white rounded-xl shadow p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <button class="floor-tab active px-5 py-2 rounded-lg bg-blue-600 text-white transition" data-floor="all">
                <i class="fas fa-layer-group mr-1"></i> All Floors ({{ $totalSlots }} slots)
            </button>
            @foreach($slotsByFloor as $floor => $floorSlots)
            <button class="floor-tab px-5 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition" data-floor="{{ $floor }}">
                <i class="fas fa-building mr-1"></i> Floor {{ $floor }}
                <span class="text-xs ml-1 bg-white/50 px-1 rounded">
                    {{ $floorSlots->where('status', 'available')->count() }}/{{ $floorSlots->count() }}
                </span>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Parking Map by Floor -->
    @foreach($slotsByFloor as $floor => $floorSlots)
    <div class="floor-section bg-white rounded-2xl shadow-xl overflow-hidden mb-8" data-floor="{{ $floor }}">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white">
                        <i class="fas fa-building mr-2"></i> Floor {{ $floor }}
                    </h2>
                    <p class="text-gray-300 text-sm mt-1">
                        <i class="fas fa-check-circle text-green-400 mr-1"></i>
                        {{ $floorSlots->where('status', 'available')->count() }} available |
                        <i class="fas fa-car text-red-400 mr-1 ml-2"></i>
                        {{ $floorSlots->where('status', 'occupied')->count() }} occupied
                    </p>
                </div>
                <div class="text-right text-white text-sm">
                    <i class="fas fa-map-pin"></i> {{ $floorSlots->count() }} total slots
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-3">
                @foreach($floorSlots as $slot)
                <div class="slot-card border-2 rounded-xl p-3 text-center cursor-pointer transition-all duration-300
                    {{ $slot->status == 'occupied' ? 'bg-gradient-to-br from-red-100 to-red-200 border-red-500' : 'bg-gradient-to-br from-green-100 to-green-200 border-green-500' }}"
                    data-slot="{{ $slot->slot_number }}"
                    data-floor="{{ $slot->floor }}"
                    data-plate="{{ $slot->activeTransaction->vehicle_plate ?? '' }}"
                    onclick="showVehicleDetails('{{ $slot->slot_number }}', '{{ $slot->floor }}', '{{ $slot->status }}', '{{ $slot->type }}', '{{ $slot->activeTransaction->vehicle_plate ?? '' }}', '{{ $slot->activeTransaction->guest_name ?? '' }}', '{{ $slot->activeTransaction->check_in ?? '' }}')">

                    <i class="fas fa-{{ $slot->status == 'occupied' ? 'car' : 'parking' }} text-2xl mb-2
                        {{ $slot->status == 'occupied' ? 'text-red-600' : 'text-green-600' }}"></i>

                    <p class="font-bold text-sm">{{ $slot->slot_number }}</p>

                    @if($slot->type == 'pwd')
                        <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded-full text-xs">
                            <i class="fas fa-wheelchair"></i>
                        </span>
                    @elseif($slot->type == 'senior')
                        <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded-full text-xs">
                            <i class="fas fa-user-graduate"></i>
                        </span>
                    @endif

                    @if($slot->status == 'occupied' && $slot->activeTransaction)
                        <p class="text-xs font-mono mt-2 text-red-600 truncate">
                            {{ $slot->activeTransaction->vehicle_plate }}
                        </p>
                    @else
                        <span class="inline-block mt-2 px-2 py-0.5 bg-green-200 text-green-800 rounded-full text-xs">
                            Available
                        </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    <!-- Legend -->
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex flex-wrap justify-center gap-6 text-sm">
            <div class="flex items-center">
                <div class="w-5 h-5 bg-green-500 rounded-full mr-2"></div>
                <span>Available Slot ({{ $availableSlots }})</span>
            </div>
            <div class="flex items-center">
                <div class="w-5 h-5 bg-red-500 rounded-full mr-2"></div>
                <span>Occupied Slot ({{ $occupiedSlots }})</span>
            </div>
            <div class="flex items-center">
                <div class="w-5 h-5 bg-yellow-500 rounded-full mr-2"></div>
                <span>PWD/Senior Priority</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-wheelchair text-purple-600 mr-2"></i>
                <span>20% Discount Available</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-hourglass-half text-orange-500 mr-2"></i>
                <span>First 30 min Free</span>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Details Modal -->
<div id="vehicleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-2xl px-6 py-4">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">
                    <i class="fas fa-info-circle mr-2"></i>Vehicle Location
                </h3>
                <button onclick="closeModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6" id="modalContent"></div>
        <div class="px-6 pb-6">
            <button onclick="closeModal()" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-300 transition">
                Close
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        this.classList.remove('bg-gray-200', 'text-gray-700');

        document.querySelectorAll('.floor-section').forEach(section => {
            if (floor === 'all' || section.dataset.floor === floor) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    });
});

// Search vehicle function
function findVehicle() {
    const plate = document.getElementById('searchPlate').value.trim().toUpperCase();
    const resultDiv = document.getElementById('searchResult');

    if (plate === '') {
        Swal.fire('Error', 'Please enter a vehicle plate number', 'error');
        return;
    }

    Swal.fire({ title: 'Searching...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

    fetch('/api/search-vehicle?plate=' + encodeURIComponent(plate))
    .then(res => res.json())
    .then(data => {
        Swal.close();

        if (data.found) {
            document.querySelectorAll('.slot-card').forEach(slot => {
                slot.classList.remove('slot-highlight');
            });

            let foundSlot = null;
            document.querySelectorAll('.slot-card').forEach(slot => {
                if (slot.dataset.plate === plate) {
                    slot.classList.add('slot-highlight');
                    foundSlot = slot;
                    slot.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });

            if (foundSlot) {
                const floor = foundSlot.dataset.floor;
                document.querySelectorAll('.floor-tab').forEach(tab => {
                    if (tab.dataset.floor === floor) tab.click();
                });
            }

            resultDiv.innerHTML = `
                <div class="bg-green-50 border-l-4 border-green-500 rounded-xl p-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 text-2xl mr-3"></i>
                        <div class="flex-1">
                            <h3 class="font-bold text-lg text-green-800">✅ Vehicle Found!</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                                <div>
                                    <p><strong>👤 Owner:</strong> ${data.guest_name}</p>
                                    <p><strong>🚗 Vehicle:</strong> ${data.vehicle_plate}</p>
                                </div>
                                <div>
                                    <p><strong>📍 Location:</strong> <span class="font-bold text-blue-600">Slot ${data.slot_number}</span></p>
                                    <p><strong>🏢 Floor:</strong> ${data.floor}</p>
                                </div>
                            </div>
                            <button onclick="showDirections('${data.slot_number}', '${data.floor}')"
                                    class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                                <i class="fas fa-directions"></i> Get Directions
                            </button>
                        </div>
                    </div>
                </div>
            `;
            resultDiv.classList.remove('hidden');
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-bold text-red-800">Vehicle Not Found</h3>
                            <p>No vehicle with plate "${plate}" is currently parked.</p>
                        </div>
                    </div>
                </div>
            `;
            resultDiv.classList.remove('hidden');
        }
    });
}

function showVehicleDetails(slotNumber, floor, status, type, plate, guestName, checkinTime) {
    const modal = document.getElementById('vehicleModal');
    const content = document.getElementById('modalContent');

    if (status === 'occupied') {
        content.innerHTML = `
            <div class="text-center">
                <i class="fas fa-car text-red-500 text-5xl mb-3"></i>
                <p class="font-bold text-xl">Slot ${slotNumber}</p>
                <p class="text-gray-600 mb-3"><i class="fas fa-building"></i> Floor: ${floor}</p>
                <div class="bg-gray-50 rounded-xl p-4 text-left">
                    <p><strong>👤 Owner:</strong> ${guestName}</p>
                    <p><strong>🚗 Vehicle:</strong> ${plate}</p>
                    <p><strong>🏷️ Type:</strong> ${type.toUpperCase()}</p>
                </div>
                <button onclick="showDirections('${slotNumber}', '${floor}')" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-xl w-full">
                    <i class="fas fa-directions"></i> Get Directions
                </button>
            </div>
        `;
    } else {
        content.innerHTML = `
            <div class="text-center">
                <i class="fas fa-parking text-green-500 text-5xl mb-3"></i>
                <p class="font-bold text-xl">Slot ${slotNumber}</p>
                <p class="text-gray-600">Floor: ${floor}</p>
                <div class="bg-green-50 rounded-xl p-4">
                    <p><strong>Status:</strong> <span class="text-green-600 font-bold">AVAILABLE</span></p>
                    <p><strong>Type:</strong> ${type.toUpperCase()}</p>
                </div>
            </div>
        `;
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function showDirections(slot, floor) {
    closeModal();
    Swal.fire({
        icon: 'info',
        title: '📍 Directions to Your Vehicle',
        html: `
            <div class="text-center">
                <p class="text-2xl text-blue-600 font-bold">Slot ${slot}</p>
                <p class="text-xl">Floor ${floor}</p>
                <hr class="my-3">
                <p class="text-sm">Go to ${floor} floor, look for Section ${slot}</p>
            </div>
        `,
        confirmButtonText: 'Got it!'
    });
}

function closeModal() {
    const modal = document.getElementById('vehicleModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('searchPlate').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') findVehicle();
});
</script>
@endsection
