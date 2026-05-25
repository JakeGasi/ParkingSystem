@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg shadow p-6 text-white mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">All Transactions</h1>
                <p class="text-blue-100">Manage all parking transactions</p>
            </div>
            <div class="text-right">
                <a href="{{ route('dashboard') }}" class="bg-white text-blue-600 px-4 py-2 rounded text-sm hover:bg-gray-100">
                    <i class="fas fa-plus mr-1"></i> New Check-in
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
        {{ session('success') }}
    </div>
    @endif

    @if(isset($transactions) && $transactions->count() > 0)
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
                        <th class="p-3 text-left">Check-out</th>
                        <th class="p-3 text-left">Amount</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ $transaction->id }}</td>
                        <td class="p-3">{{ $transaction->guest_name }}</td>
                        <td class="p-3 font-mono">{{ $transaction->vehicle_plate }}</td>
                        <td class="p-3">
                            @if($transaction->user_type == 'pwd')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">PWD</span>
                            @elseif($transaction->user_type == 'senior')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Senior</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">Regular</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $transaction->parkingSlot->slot_number ?? 'N/A' }}</td>
                        <td class="p-3">{{ $transaction->parkingSlot->floor ?? 'N/A' }}</td>
                        <td class="p-3">{{ $transaction->check_in ? $transaction->check_in->format('h:i A') : '-' }}</td>
                        <td class="p-3">{{ $transaction->check_out ? $transaction->check_out->format('h:i A') : '-' }}</td>
                        <td class="p-3 text-green-600 font-bold">${{ number_format($transaction->amount, 2) }}</td>
                        <td class="p-3">
                            @if($transaction->status == 'active')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Active</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">Completed</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($transaction->status == 'active')
                            <button onclick="checkoutTransaction({{ $transaction->id }})" class="bg-green-500 text-white px-2 py-1 rounded text-xs hover:bg-green-600">
                                <i class="fas fa-receipt"></i> Checkout
                            </button>
                            @endif
                            <a href="{{ url('/transactions/' . $transaction->id . '/edit') }}" class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button onclick="deleteTransaction({{ $transaction->id }})" class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $transactions->links() }}
        </div>
    </div>
    @else
    <div class="bg-yellow-100 p-6 text-center rounded">
        <p class="text-lg">No transactions found.</p>
        <a href="{{ route('dashboard') }}" class="inline-block mt-3 bg-blue-500 text-white px-4 py-2 rounded">Go to Dashboard</a>
    </div>
    @endif
</div>

<script>
function checkoutTransaction(id) {
    Swal.fire({
        title: 'Checkout Vehicle?',
        text: "Calculate payment and free the slot",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, Checkout'
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

function deleteTransaction(id) {
    Swal.fire({
        title: 'Delete Transaction?',
        text: "This cannot be undone",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/transactions/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    Swal.fire('Deleted!', '', 'success').then(() => location.reload());
                }
            });
        }
    });
}
</script>
@endsection
