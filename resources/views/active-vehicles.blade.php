@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="bg-purple-600 rounded-lg shadow p-6 text-white mb-6">
        <h1 class="text-3xl font-bold">Active Vehicles</h1>
        <p>Currently parked vehicles - {{ $activeVehicles->count() }} total</p>
    </div>

    @if($activeVehicles->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Guest</th>
                        <th class="p-3 text-left">Plate</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Slot</th>
                        <th class="p-3 text-left">Floor</th>
                        <th class="p-3 text-left">Check-in</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeVehicles as $vehicle)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ $vehicle->id }}</td>
                        <td class="p-3">{{ $vehicle->guest_name }}</td>
                        <td class="p-3 font-mono">{{ $vehicle->vehicle_plate }}</td>
                        <td class="p-3">{{ ucfirst($vehicle->user_type) }}</td>
                        <td class="p-3">{{ $vehicle->parkingSlot->slot_number ?? 'N/A' }} ({!! $vehicle->parkingSlot->floor ?? 'N/A' !!})</td>
                        <td class="p-3">{{ $vehicle->check_in->format('h:i A') }}</td>
                        <td class="p-3" id="duration_{{ $vehicle->id }}">-</td>
                        <td class="p-3">
                            <button onclick="checkoutVehicle({{ $vehicle->id }})" class="bg-red-500 text-white px-3 py-1 rounded text-sm">Checkout</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-yellow-100 p-6 text-center rounded">
        <p>No active vehicles found.</p>
        <a href="{{ route('dashboard') }}" class="inline-block mt-3 bg-blue-500 text-white px-4 py-2 rounded">Go to Dashboard</a>
    </div>
    @endif
</div>

<script>
function updateDurations() {
    @foreach($activeVehicles as $vehicle)
    (function() {
        const checkinTime = new Date('{{ $vehicle->check_in->toISOString() }}');
        const now = new Date();
        const minutes = Math.floor((now - checkinTime) / 60000);
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        const elem = document.getElementById('duration_{{ $vehicle->id }}');
        if (elem) elem.innerHTML = `${hours > 0 ? hours + 'h ' : ''}${mins}m`;
    })();
    @endforeach
}
setInterval(updateDurations, 60000);
updateDurations();

function checkoutVehicle(id) {
    Swal.fire({
        title: 'Checkout Vehicle?',
        text: "Calculate payment and free the slot",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Processing...', didOpen: () => Swal.showLoading() });
            $.ajax({
                url: '/checkout/' + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'PUT' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', `Amount: $${response.amount}`, 'success').then(() => location.reload());
                    }
                },
                error: function() { Swal.fire('Error', 'Checkout failed', 'error'); }
            });
        }
    });
}
</script>
@endsection
