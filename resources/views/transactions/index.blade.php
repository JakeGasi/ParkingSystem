@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4"><i class="fas fa-list"></i> All Transactions (CRUD)</h2>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">ID</th>
                    <th class="p-2">Guest</th>
                    <th class="p-2">Plate</th>
                    <th class="p-2">Type</th>
                    <th class="p-2">Slot</th>
                    <th class="p-2">Check-in</th>
                    <th class="p-2">Check-out</th>
                    <th class="p-2">Amount</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $trans)
                <tr class="border-b">
                    <td class="p-2">{{ $trans->id }}</td>
                    <td class="p-2">{{ $trans->guest_name }}</td>
                    <td class="p-2">{{ $trans->vehicle_plate }}</td>
                    <td class="p-2">{{ ucfirst($trans->user_type) }}</td>
                    <td class="p-2">{{ $trans->parkingSlot->slot_number ?? 'N/A' }}</td>
                    <td class="p-2">{{ $trans->check_in->format('Y-m-d H:i') }}</td>
                    <td class="p-2">{{ $trans->check_out ? $trans->check_out->format('Y-m-d H:i') : '-' }}</td>
                    <td class="p-2">${{ number_format($trans->amount, 2) }}</td>
                    <td class="p-2">
                        <a href="{{ route('transactions.edit', $trans->id) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                        <button onclick="deleteTrans({{ $trans->id }})" class="text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $transactions->links() }}
</div>

@push('scripts')
<script>
function deleteTrans(id) {
    Swal.fire({
        title: 'Delete?',
        text: "This action cannot be undone",
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("/transactions") }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    Swal.fire('Deleted!', '', 'success');
                    location.reload();
                }
            });
        }
    });
}
</script>
@endpush
@endsection
