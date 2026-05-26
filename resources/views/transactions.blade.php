@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow p-6 text-white mb-6">
        <h1 class="text-3xl font-bold">All Transactions</h1>
        <p>Manage all parking transactions</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Guest</th>
                    <th class="p-3">Plate</th>
                    <th class="p-3">Type</th>
                    <th class="p-3">Slot</th>
                    <th class="p-3">Check-in</th>
                    <th class="p-3">Check-out</th>
                    <th class="p-3">Amount</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr class="border-b">
                    <td class="p-3">{{ $transaction->id }}</td>
                    <td class="p-3">{{ $transaction->guest_name }}</td>
                    <td class="p-3">{{ $transaction->vehicle_plate }}</td>
                    <td class="p-3">{{ ucfirst($transaction->user_type) }}</td>
                    <td class="p-3">{{ $transaction->parkingSlot->slot_number ?? 'N/A' }}</td>
                    <td class="p-3">{{ $transaction->check_in ? $transaction->check_in->format('h:i A') : '-' }}</td>
                    <td class="p-3">{{ $transaction->check_out ? $transaction->check_out->format('h:i A') : '-' }}</td>
                    <td class="p-3 text-green-600">${{ number_format($transaction->amount, 2) }}</td>
                    <td class="p-3">
                        @if($transaction->status == 'active')
                            <span class="bg-green-100 px-2 py-1 rounded text-xs">Active</span>
                        @else
                            <span class="bg-gray-100 px-2 py-1 rounded text-xs">Completed</span>
                        @endif
                    </td>
                    <td class="p-3">
                        <a href="{{ url('/transactions/' . $transaction->id . '/edit') }}" class="text-blue-600 mr-2">Edit</a>
                        <button onclick="deleteTrans({{ $transaction->id }})" class="text-red-600">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $transactions->links() }}</div>
    </div>
</div>

<script>
function deleteTrans(id) {
    if(confirm('Delete this transaction?')) {
        $.ajax({
            url: '/transactions/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: () => location.reload()
        });
    }
}
</script>
@endsection
