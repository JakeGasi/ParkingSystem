@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="bg-gradient-to-r from-green-600 to-emerald-700 rounded-lg shadow p-6 text-white mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Payment History</h1>
                <p class="text-green-100">All completed transactions</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">${{ number_format($totalRevenue ?? 0, 2) }}</div>
                <div class="text-sm">Total Revenue</div>
            </div>
        </div>
    </div>

    @if(isset($completedTransactions) && $completedTransactions->count() > 0)
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
                        <th class="p-3 text-left">Duration</th>
                        <th class="p-3 text-left">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completedTransactions as $transaction)
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
                        <td class="p-3 font-mono">{{ $transaction->parkingSlot->slot_number ?? 'N/A' }}</td>
                        <td class="p-3">{{ $transaction->parkingSlot->floor ?? 'N/A' }}</td>
                        <td class="p-3">{{ $transaction->check_in ? $transaction->check_in->format('h:i A') : '-' }}</td>
                        <td class="p-3">{{ $transaction->check_out ? $transaction->check_out->format('h:i A') : '-' }}</td>
                        <td class="p-3">
                            @php
                                if ($transaction->check_in && $transaction->check_out) {
                                    $duration = $transaction->check_in->diffInMinutes($transaction->check_out);
                                    $hours = floor($duration / 60);
                                    $minutes = $duration % 60;
                                    echo ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm';
                                } else {
                                    echo '-';
                                }
                            @endphp
                        </td>
                        <td class="p-3 text-green-600 font-bold">${{ number_format($transaction->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $completedTransactions->links() }}
        </div>
    </div>
    @else
    <div class="bg-yellow-100 p-6 text-center rounded">
        <p class="text-lg">No payment history found.</p>
    </div>
    @endif
</div>
@endsection
