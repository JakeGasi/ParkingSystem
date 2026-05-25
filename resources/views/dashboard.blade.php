@extends('layouts.simple')

@section('content')
<div class="space-y-6">

    <!-- Progress Bar for Parking Capacity -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-2">
            <h3 class="font-bold text-gray-700">
                <i class="fas fa-chart-line mr-2 text-blue-500"></i>
                Parking Capacity: {{ $occupiedSlots }} / 50 Vehicles
            </h3>
            <span class="text-sm font-semibold {{ $occupiedSlots >= 50 ? 'text-red-600' : ($occupiedSlots >= 40 ? 'text-yellow-600' : 'text-green-600') }}">
                {{ round(($occupiedSlots / 50) * 100) }}% Full
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div class="h-4 rounded-full transition-all duration-500
                {{ $occupiedSlots >= 50 ? 'bg-red-600' : ($occupiedSlots >= 40 ? 'bg-yellow-500' : 'bg-green-500') }}"
                style="width: {{ ($occupiedSlots / 50) * 100 }}%">
            </div>
        </div>
        @if($occupiedSlots >= 50)
            <div class="mt-3 bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded alert-animation">
                <div class="flex items-center">
                    <i class="fas fa-ban text-2xl mr-3"></i>
                    <div>
                        <strong>⚠️ PARKING IS FULL!</strong>
                        <p class="text-sm">Maximum capacity of 50 vehicles reached. No check-ins allowed until a vehicle checks out.</p>
                    </div>
                </div>
            </div>
        @elseif($occupiedSlots >= 40)
            <div class="mt-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-3 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-xl mr-3"></i>
                    <div>
                        <strong>⚠️ Limited Slots Available!</strong>
                        <p class="text-sm">Only {{ 50 - $occupiedSlots }} slots remaining. Parking is filling up fast!</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <i class="fas fa-parking text-blue-500 text-3xl"></i>
            <p class="text-2xl font-bold mt-2">{{ $totalSlots }}</p>
            <p class="text-gray-500">Total Slots</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <i class="fas fa-car text-red-500 text-3xl"></i>
            <p class="text-2xl font-bold mt-2">{{ $occupiedSlots }}</p>
            <p class="text-gray-500">Occupied</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <i class="fas fa-check-circle text-green-500 text-3xl"></i>
            <p class="text-2xl font-bold mt-2">{{ 50 - $occupiedSlots }}</p>
            <p class="text-gray-500">Available</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <i class="fas fa-users text-purple-500 text-3xl"></i>
            <p class="text-2xl font-bold mt-2">{{ $activeVehicles }}</p>
            <p class="text-gray-500">Active Vehicles</p>
        </div>
        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg shadow p-6 text-white">
            <i class="fas fa-dollar-sign text-3xl"></i>
            <p class="text-2xl font-bold mt-2">${{ number_format($todayRevenue, 2) }}</p>
            <p class="text-sm opacity-90">Today's Revenue</p>
        </div>
    </div>

    <!-- Check-in Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4"><i class="fas fa-sign-in-alt mr-2 text-blue-500"></i>Check-in Vehicle</h2>

        @if($occupiedSlots >= 50)
        <div class="bg-red-50 border border-red-300 rounded-lg p-4 text-center">
            <i class="fas fa-ban text-red-500 text-4xl mb-2"></i>
            <p class="text-red-600 font-bold">PARKING FULL</p>
            <p class="text-red-500 text-sm">Cannot accept new check-ins. Maximum capacity reached.</p>
        </div>
        @else
        <form id="checkinForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <input type="text" id="guest_name" placeholder="Guest Name" class="border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <input type="text" id="vehicle_plate" placeholder="Vehicle Plate" class="border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <select id="user_type" class="border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="regular">Regular</option>
                <option value="pwd">PWD (20% discount)</option>
                <option value="senior">Senior Citizen (20% discount)</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white rounded-lg p-2 hover:bg-blue-700 transition">
                <i class="fas fa-parking"></i> Check-in
            </button>
        </form>
        <div class="mt-3 text-sm text-gray-500">
            <i class="fas fa-info-circle"></i> Available slots: <strong class="text-green-600">{{ 50 - $occupiedSlots }}</strong> out of 50
        </div>
        @endif
    </div>

    <!-- Parking Map -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4"><i class="fas fa-map"></i> Parking Map - Find Your Vehicle</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-2">
            @foreach($slots as $slot)
            <div class="border rounded-lg p-2 text-center cursor-pointer hover:shadow-lg transition slot-card text-xs
                {{ $slot->status == 'occupied' ? 'bg-red-100 border-red-400' : ($slot->type == 'pwd' || $slot->type == 'senior' ? 'bg-yellow-100 border-yellow-400' : 'bg-green-100 border-green-400') }}">
                <i class="fas fa-{{ $slot->status == 'occupied' ? 'car' : 'parking' }} text-lg"></i>
                <p class="font-bold text-xs">{{ $slot->slot_number }}</p>
                <p class="text-xs">Floor: {{ $slot->floor }}</p>
                @if($slot->status == 'occupied' && $slot->activeTransaction)
                    <p class="text-xs truncate font-mono">{{ $slot->activeTransaction->vehicle_plate }}</p>
                @endif
            </div>
            @endforeach
        </div>
        <div class="mt-4 text-sm text-gray-500 flex flex-wrap gap-4">
            <span><i class="fas fa-square text-green-400"></i> Available ({{ 50 - $occupiedSlots }})</span>
            <span><i class="fas fa-square text-red-400"></i> Occupied ({{ $occupiedSlots }})</span>
            <span><i class="fas fa-square text-yellow-400"></i> PWD/Senior Priority</span>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4"><i class="fas fa-history"></i> Recent Activity</h2>

        @if(isset($recentTransactions) && $recentTransactions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">Guest</th>
                        <th class="p-2 text-left">Plate</th>
                        <th class="p-2 text-left">Type</th>
                        <th class="p-2 text-left">Slot</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $trans)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2">{{ $trans->guest_name }}</td>
                        <td class="p-2 font-mono text-sm">{{ $trans->vehicle_plate }}</td>
                        <td class="p-2">{{ ucfirst($trans->user_type) }}</td>
                        <td class="p-2">{{ $trans->parkingSlot->slot_number ?? 'N/A' }} ({!! $trans->parkingSlot->floor ?? 'N/A' !!})</td>
                        <td class="p-2">
                            <span class="px-2 py-1 rounded text-xs {{ $trans->status == 'active' ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-800' }}">
                                {{ ucfirst($trans->status) }}
                            </span>
                        </td>
                        <td class="p-2">
                            @if($trans->status == 'active')
                            <button onclick="checkoutVehicle({{ $trans->id }})" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition">
                                <i class="fas fa-sign-out-alt"></i> Check-out
                            </button>
                            @else
                            <span class="text-green-600 font-semibold">${{ number_format($trans->amount, 2) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-receipt text-4xl mb-2"></i>
            <p>No recent transactions</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Check-in Form
$('#checkinForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

    $.post('/checkin', {
        guest_name: $('#guest_name').val(),
        vehicle_plate: $('#vehicle_plate').val(),
        user_type: $('#user_type').val(),
        _token: '{{ csrf_token() }}'
    }, function(response) {
        Swal.fire('Success!', `Slot: ${response.slot} (Floor ${response.floor})`, 'success')
            .then(() => location.reload());
    }).fail(function(xhr) {
        Swal.fire('Error!', xhr.responseJSON?.error || 'Check-in failed', 'error');
        btn.prop('disabled', false).html('<i class="fas fa-parking"></i> Check-in');
    });
});

// Checkout Function
function checkoutVehicle(id) {
    Swal.fire({
        title: 'Check-out vehicle?',
        text: "Calculate payment and free the slot",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, check out!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '/checkout/' + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'PUT' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', `Amount: $${response.amount}`, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Checkout failed', 'error');
                }
            });
        }
    });
}
</script>
@endpush
@endsection
