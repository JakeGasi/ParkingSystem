@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="bg-purple-600 rounded-xl shadow p-6 text-white mb-6">
        <h1 class="text-3xl font-bold">Active Vehicles</h1>
        <p>Currently parked vehicles - {{ $activeVehicles->count() }} total</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Guest</th>
                    <th class="p-3">Plate</th>
                    <th class="p-3">Type</th>
                    <th class="p-3">Slot</th>
                    <th class="p-3">Check-in</th>
                    <th class="p-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeVehicles as $vehicle)
                <tr class="border-b">
                    <td class="p-3">{{ $vehicle->guest_name }}</td>
                    <td class="p-3 font-mono">{{ $vehicle->vehicle_plate }}</td>
                    <td class="p-3">{{ ucfirst($vehicle->user_type) }}</td>
                    <td class="p-3">{{ $vehicle->parkingSlot->slot_number ?? 'N/A' }} ({!! $vehicle->parkingSlot->floor ?? 'N/A' !!})</td>
                    <td class="p-3">{{ $vehicle->check_in->format('h:i A') }}</td>
                    <td class="p-3">
                        <button onclick="checkout({{ $vehicle->id }})" class="bg-red-500 text-white px-3 py-1 rounded text-sm">Checkout</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function checkout(id) {
    Swal.fire({
        title: 'Checkout?',
        text: 'Calculate payment?',
        icon: 'question',
        showCancelButton: true
    }).then((result) => {
        if(result.isConfirmed) {
            Swal.fire({ title: 'Processing...', didOpen: () => Swal.showLoading() });
            $.post('/checkout/' + id, { _token: '{{ csrf_token() }}', _method: 'PUT' }, function(response) {
                Swal.fire('Success!', `Amount: $${response.amount}`, 'success').then(() => location.reload());
            });
        }
    });
}
</script>
@endsection
