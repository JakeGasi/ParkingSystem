<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Map - Find Your Vehicle | MallPark</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            background: linear-gradient(135deg, #dbeafe, #bfdbfe) !important;
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .search-box:focus {
            box-shadow: 0 0 0 3px rgba(59,130,246,0.3);
            border-color: #3b82f6;
        }
        .floor-section {
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-2">
                        <i class="fas fa-map-marked-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-xl text-gray-800">Find My Vehicle</span>
                        <span class="text-xs text-gray-500 block">Locate your parked vehicle</span>
                    </div>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-blue-600 transition">
                        <i class="fas fa-chart-line mr-1"></i> Dashboard
                    </a>
                    <a href="{{ url('/user-map') }}" class="text-blue-600 border-b-2 border-blue-600 font-medium">
                        <i class="fas fa-map mr-1"></i> Find Vehicle
                    </a>
                    <a href="{{ url('/active-vehicles') }}" class="text-gray-700 hover:text-blue-600 transition">
                        <i class="fas fa-car-side mr-1"></i> Active Vehicles
                    </a>
                    <a href="{{ url('/transactions') }}" class="text-gray-700 hover:text-blue-600 transition">
                        <i class="fas fa-list mr-1"></i> Transactions
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">

        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-green-600 to-teal-700 rounded-2xl shadow-xl p-6 text-white mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">
                        <i class="fas fa-map-marked-alt mr-3"></i>Parking Map - Find Your Vehicle
                    </h1>
                    <p class="text-green-100 mt-2">Enter your plate number or browse the map to locate your vehicle</p>
                </div>
                <div class="mt-4 md:mt-0 text-center bg-white/20 rounded-xl px-6 py-3">
                    <div class="text-2xl font-bold">{{ $activeVehicles->count() }}</div>
                    <div class="text-sm opacity-90">Vehicles Parked</div>
                    <div class="text-xs opacity-75">{{ $availableSlots }} slots available</div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-search text-blue-500 mr-2"></i>Search Your Vehicle
            </h2>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchPlate"
                           placeholder="Enter your vehicle plate number (e.g., ABC-1234)"
                           class="search-box w-full border-2 border-gray-200 rounded-xl pl-12 pr-4 py-4 text-lg focus:outline-none focus:border-blue-500">
                </div>
                <button onclick="findVehicle()"
                        class="bg-blue-600 text-white px-8 py-4 rounded-xl font-semibold hover:bg-blue-700 transition transform hover:scale-105">
                    <i class="fas fa-location-dot mr-2"></i>Find My Vehicle
                </button>
            </div>
            <div id="searchResult" class="mt-4 hidden"></div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl shadow-lg p-4 text-white text-center transform hover:scale-105 transition">
                <i class="fas fa-wheelchair text-3xl mb-2"></i>
                <p class="text-3xl font-bold">{{ $pwdCount ?? 0 }}</p>
                <p class="text-sm">PWD Parked</p>
                <p class="text-xs opacity-75">20% discount</p>
            </div>
            <div class="bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl shadow-lg p-4 text-white text-center transform hover:scale-105 transition">
                <i class="fas fa-user-graduate text-3xl mb-2"></i>
                <p class="text-3xl font-bold">{{ $seniorCount ?? 0 }}</p>
                <p class="text-sm">Senior Citizens</p>
                <p class="text-xs opacity-75">20% discount</p>
            </div>
            <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-xl shadow-lg p-4 text-white text-center transform hover:scale-105 transition">
                <i class="fas fa-car text-3xl mb-2"></i>
                <p class="text-3xl font-bold">{{ $activeVehicles->count() }}</p>
                <p class="text-sm">Total Parked</p>
                <p class="text-xs opacity-75">Currently in mall</p>
            </div>
            <div class="bg-gradient-to-r from-purple-400 to-purple-500 rounded-xl shadow-lg p-4 text-white text-center transform hover:scale-105 transition">
                <i class="fas fa-clock text-3xl mb-2"></i>
                <p class="text-3xl font-bold" id="liveTime">{{ date('h:i A') }}</p>
                <p class="text-sm">Current Time</p>
                <p class="text-xs opacity-75">{{ date('l') }}</p>
            </div>
        </div>

        <!-- Floor Navigation Tabs -->
        <div class="bg-white rounded-xl shadow-lg mb-6 p-4">
            <div class="flex flex-wrap gap-2">
                <button class="floor-tab active px-5 py-2 rounded-lg bg-blue-600 text-white transition" data-floor="all">
                    <i class="fas fa-layer-group mr-1"></i> All Floors
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
            <!-- Floor Header -->
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

            <!-- Slots Grid -->
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-3">
                    @foreach($floorSlots as $slot)
                    <div class="slot-card border-2 rounded-xl p-3 text-center cursor-pointer transition-all duration-300
                        {{ $slot->status == 'occupied' ? 'bg-gradient-to-br from-red-50 to-red-100 border-red-400' : 'bg-gradient-to-br from-green-50 to-green-100 border-green-400' }}"
                        data-slot="{{ $slot->slot_number }}"
                        data-floor="{{ $slot->floor }}"
                        data-plate="{{ $slot->activeTransaction->vehicle_plate ?? '' }}"
                        onclick="showVehicleDetails({{ $slot->id }}, '{{ $slot->slot_number }}', '{{ $slot->floor }}', '{{ $slot->status }}', '{{ $slot->type }}', '{{ $slot->activeTransaction->vehicle_plate ?? '' }}', '{{ $slot->activeTransaction->guest_name ?? '' }}', '{{ $slot->activeTransaction->check_in ?? '' }}')">

                        <!-- Icon -->
                        <i class="fas fa-{{ $slot->status == 'occupied' ? 'car' : 'parking' }} text-2xl mb-2
                            {{ $slot->status == 'occupied' ? 'text-red-600' : 'text-green-600' }}"></i>

                        <!-- Slot Number -->
                        <p class="font-bold text-sm">{{ $slot->slot_number }}</p>

                        <!-- Priority Badge -->
                        @if($slot->type == 'pwd')
                            <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded-full text-xs">
                                <i class="fas fa-wheelchair"></i> PWD
                            </span>
                        @elseif($slot->type == 'senior')
                            <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded-full text-xs">
                                <i class="fas fa-user-graduate"></i> Senior
                            </span>
                        @endif

                        <!-- Vehicle Plate (if occupied) -->
                        @if($slot->status == 'occupied' && $slot->activeTransaction)
                            <p class="text-xs font-mono mt-2 text-red-600 truncate" title="{{ $slot->activeTransaction->vehicle_plate }}">
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
                    <i class="fas fa-wheelchair text-purple-600 mr-2 text-lg"></i>
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
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
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

    <script>
    // Live clock update
    function updateClock() {
        const now = new Date();
        document.getElementById('liveTime').innerHTML = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Floor tabs functionality
    document.querySelectorAll('.floor-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const floor = this.dataset.floor;

            // Update active tab style
            document.querySelectorAll('.floor-tab').forEach(t => {
                t.classList.remove('active', 'bg-blue-600', 'text-white');
                t.classList.add('bg-gray-200', 'text-gray-700');
            });
            this.classList.add('active', 'bg-blue-600', 'text-white');
            this.classList.remove('bg-gray-200', 'text-gray-700');

            // Show/hide floor sections
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
                // Remove existing highlights
                document.querySelectorAll('.slot-card').forEach(slot => {
                    slot.classList.remove('slot-highlight');
                });

                // Find and highlight the matching slot
                let foundSlot = null;
                document.querySelectorAll('.slot-card').forEach(slot => {
                    if (slot.dataset.plate === plate) {
                        slot.classList.add('slot-highlight');
                        foundSlot = slot;
                        // Scroll to the slot
                        slot.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });

                if (foundSlot) {
                    // Get the floor from the slot and show that floor
                    const floor = foundSlot.dataset.floor;
                    document.querySelectorAll('.floor-tab').forEach(tab => {
                        if (tab.dataset.floor === floor) {
                            tab.click();
                        }
                    });
                }

                resultDiv.innerHTML = `
                    <div class="bg-green-50 border-l-4 border-green-500 rounded-xl p-4 animate-fade-in">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 text-2xl mr-3"></i>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-green-800">✅ Vehicle Found!</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                                    <div>
                                        <p><strong>👤 Owner:</strong> ${data.guest_name}</p>
                                        <p><strong>🚗 Vehicle:</strong> ${data.vehicle_plate}</p>
                                        <p><strong>🏷️ Type:</strong> ${data.user_type.toUpperCase()}</p>
                                    </div>
                                    <div>
                                        <p><strong>📍 Location:</strong> <span class="font-bold text-blue-600">Slot ${data.slot_number}</span></p>
                                        <p><strong>🏢 Floor:</strong> ${data.floor}</p>
                                        <p><strong>🕐 Parked:</strong> ${data.check_in_time} (${data.duration} ago)</p>
                                    </div>
                                </div>
                                <button onclick="showDirections('${data.slot_number}', '${data.floor}')"
                                        class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                                    <i class="fas fa-directions"></i> Get Directions to Vehicle
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                resultDiv.classList.remove('hidden');

                Swal.fire({
                    icon: 'success',
                    title: 'Vehicle Located!',
                    text: `Your vehicle is at Slot ${data.slot_number} on Floor ${data.floor}`,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                resultDiv.innerHTML = `
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                            <div>
                                <h3 class="font-bold text-red-800">Vehicle Not Found</h3>
                                <p>No vehicle with plate "${plate}" is currently parked in the mall.</p>
                                <p class="text-sm text-red-600 mt-1">Please check the plate number and try again.</p>
                            </div>
                        </div>
                    </div>
                `;
                resultDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Error', 'Search failed. Please try again.', 'error');
        });
    }

    // Show vehicle details
    function showVehicleDetails(id, slotNumber, floor, status, type, plate, guestName, checkinTime) {
        const modal = document.getElementById('vehicleModal');
        const content = document.getElementById('modalContent');

        if (status === 'occupied') {
            const checkin = new Date(checkinTime);
            const now = new Date();
            const minutes = Math.floor((now - checkin) / 60000);
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;

            let amount = 0;
            if (minutes > 30) {
                let billableHours = Math.ceil((minutes - 30) / 60);
                amount = billableHours * 2;
                if (type === 'pwd' || type === 'senior') {
                    amount = amount * 0.8;
                }
                amount = amount.toFixed(2);
            }

            let priorityBadge = '';
            if (type === 'pwd') {
                priorityBadge = '<span class="inline-block mt-2 px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs"><i class="fas fa-wheelchair"></i> Priority slot - 20% discount</span>';
            } else if (type === 'senior') {
                priorityBadge = '<span class="inline-block mt-2 px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs"><i class="fas fa-user-graduate"></i> Priority slot - 20% discount</span>';
            }

            content.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-car text-red-500 text-5xl mb-3"></i>
                    <p class="font-bold text-xl">Slot ${slotNumber}</p>
                    <p class="text-gray-600 mb-3"><i class="fas fa-building"></i> Floor: ${floor}</p>
                    <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2">
                        <p><strong>👤 Owner:</strong> ${guestName}</p>
                        <p><strong>🚗 Vehicle:</strong> ${plate}</p>
                        <p><strong>🏷️ Type:</strong> ${type.toUpperCase()}</p>
                        <p><strong>🕐 Check-in:</strong> ${checkin.toLocaleTimeString()}</p>
                        <p><strong>⏱️ Duration:</strong> <span class="font-bold text-purple-600">${hours > 0 ? hours + 'h ' : ''}${mins}m</span></p>
                        <p><strong>💰 Est. Amount:</strong> <span class="font-bold text-green-600">$${amount}</span></p>
                        ${priorityBadge}
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button onclick="showDirections('${slotNumber}', '${floor}')"
                                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700">
                            <i class="fas fa-directions"></i> Directions
                        </button>
                        <button onclick="checkoutVehicle(${id})"
                                class="flex-1 bg-red-500 text-white px-4 py-2 rounded-xl hover:bg-red-600">
                            <i class="fas fa-receipt"></i> Checkout
                        </button>
                    </div>
                </div>
            `;
        } else {
            let priorityText = '';
            if (type === 'pwd') {
                priorityText = '<p class="text-yellow-600 mt-2"><i class="fas fa-wheelchair"></i> Priority slot for PWD - 20% discount available</p>';
            } else if (type === 'senior') {
                priorityText = '<p class="text-yellow-600 mt-2"><i class="fas fa-user-graduate"></i> Priority slot for Senior Citizens - 20% discount available</p>';
            }

            content.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-parking text-green-500 text-5xl mb-3"></i>
                    <p class="font-bold text-xl">Slot ${slotNumber}</p>
                    <p class="text-gray-600 mb-3">Floor: ${floor}</p>
                    <div class="bg-green-50 rounded-xl p-4">
                        <p><strong>Status:</strong> <span class="text-green-600 font-bold">AVAILABLE</span></p>
                        <p><strong>Type:</strong> ${type.toUpperCase()}</p>
                        ${priorityText}
                    </div>
                    <button onclick="window.location.href='/dashboard'" class="mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700">
                        <i class="fas fa-parking"></i> Check-in Here
                    </button>
                </div>
            `;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('vehicleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function showDirections(slot, floor) {
        closeModal();
        Swal.fire({
            icon: 'info',
            title: '📍 Directions to Your Vehicle',
            html: `
                <div class="text-center">
                    <i class="fas fa-map-marker-alt text-blue-500 text-4xl mb-3"></i>
                    <p class="text-lg font-bold">Your vehicle is located at:</p>
                    <div class="bg-blue-50 rounded-lg p-3 my-3">
                        <p class="text-2xl text-blue-600 font-bold">Slot ${slot}</p>
                        <p class="text-xl">Floor ${floor}</p>
                    </div>
                    <div class="text-left text-sm text-gray-600 space-y-2">
                        <p><i class="fas fa-arrow-right text-green-500"></i> Take the elevator to <strong>Floor ${floor}</strong></p>
                        <p><i class="fas fa-arrow-right text-green-500"></i> Follow the signs to <strong>Section ${slot}</strong></p>
                        <p><i class="fas fa-arrow-right text-green-500"></i> Your vehicle is in parking spot <strong>${slot}</strong></p>
                    </div>
                </div>
            `,
            confirmButtonText: 'Got it! Thanks',
            confirmButtonColor: '#3b82f6'
        });
    }

    function checkoutVehicle(id) {
        closeModal();
        Swal.fire({
            title: 'Checkout Vehicle',
            text: 'Calculate payment and free the parking slot?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Checkout'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Processing...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

                $.ajax({
                    url: '/checkout/' + id,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'PUT' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '✅ Payment Successful!',
                                html: `
                                    <div class="text-left">
                                        <p><strong>${response.guest_name}</strong></p>
                                        <p>Vehicle: ${response.vehicle_plate}</p>
                                        <hr class="my-2">
                                        <p>Duration: <strong>${response.duration}</strong></p>
                                        <p class="text-2xl text-green-600 font-bold mt-2">$${response.amount}</p>
                                    </div>
                                `,
                                confirmButtonText: 'OK'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Checkout failed', 'error'); }
                });
            }
        });
    }

    // Enter key search
    document.getElementById('searchPlate').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') findVehicle();
    });
    </script>
</body>
</html>
