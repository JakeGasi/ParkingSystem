@extends('layouts.app')

@section('content')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .duration-update {
        transition: all 0.3s ease;
        font-family: 'Courier New', monospace;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .transaction-row {
        animation: fadeIn 0.3s ease;
    }
    .receipt-modal {
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .success-alert {
        animation: slideIn 0.5s ease;
    }
</style>

<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-700 rounded-2xl shadow-xl p-6 text-white mb-8">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold">
                    <i class="fas fa-history mr-3"></i>Payment History
                </h1>
                <p class="text-green-100 mt-1">All completed parking transactions</p>
            </div>
            <div class="bg-white/20 rounded-xl px-6 py-3 text-center backdrop-blur">
                <div class="text-3xl font-bold">${{ number_format($totalRevenue, 2) }}</div>
                <div class="text-sm opacity-90">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Success Message after Checkout -->
    @if(session('checkout_success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-md success-alert">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 text-2xl mr-3"></i>
            <div>
                <strong class="text-lg">✅ Payment Successful!</strong>
                <p>{{ session('checkout_success') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-md">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-3"></i>
            <div>
                <strong>Error!</strong>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <div class="stat-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 text-white text-center">
            <i class="fas fa-receipt text-3xl mb-2"></i>
            <p class="text-3xl font-bold">{{ $totalTransactions }}</p>
            <p class="text-sm opacity-90">Total Transactions</p>
        </div>
        <div class="stat-card bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-5 text-white text-center">
            <i class="fas fa-dollar-sign text-3xl mb-2"></i>
            <p class="text-3xl font-bold">${{ number_format($totalRevenue, 2) }}</p>
            <p class="text-sm opacity-90">Total Revenue</p>
        </div>
        <div class="stat-card bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl shadow-lg p-5 text-white text-center">
            <i class="fas fa-calendar-day text-3xl mb-2"></i>
            <p class="text-3xl font-bold">${{ number_format($todayRevenue, 2) }}</p>
            <p class="text-sm opacity-90">Today's Revenue</p>
        </div>
        <div class="stat-card bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-5 text-white text-center">
            <i class="fas fa-chart-line text-3xl mb-2"></i>
            <p class="text-3xl font-bold">${{ number_format($averageRevenue, 2) }}</p>
            <p class="text-sm opacity-90">Average per Transaction</p>
        </div>
    </div>

    <!-- Active Vehicles Section (Real-time duration) -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white">
                <i class="fas fa-clock mr-2"></i>Currently Parked (Real-time)
            </h2>
            <p class="text-blue-100 text-sm mt-1">These vehicles are currently parked - duration updates automatically</p>
        </div>

        @if($activeTransactions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Guest</th>
                        <th class="p-3 text-left">Vehicle Plate</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Slot</th>
                        <th class="p-3 text-left">Check-in</th>
                        <th class="p-3 text-left">Current Duration</th>
                        <th class="p-3 text-left">Est. Amount</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeTransactions as $transaction)
                    <tr class="border-b hover:bg-gray-50 transition transaction-row" id="active-row-{{ $transaction->id }}">
                        <td class="p-3 font-medium">{{ $transaction->guest_name }}</td>
                        <td class="p-3 font-mono font-bold">{{ $transaction->vehicle_plate }}</td>
                        <td class="p-3">
                            @if($transaction->user_type == 'pwd')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">
                                    <i class="fas fa-wheelchair"></i> PWD
                                </span>
                            @elseif($transaction->user_type == 'senior')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">
                                    <i class="fas fa-user-graduate"></i> Senior
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">Regular</span>
                            @endif
                        </td>
                        <td class="p-3 font-mono">{{ $transaction->parkingSlot->slot_number ?? 'N/A' }} ({{ $transaction->parkingSlot->floor ?? 'N/A' }})</td>
                        <td class="p-3">{{ $transaction->check_in->format('h:i A') }}</td>
                        <td class="p-3">
                            <span class="duration-update text-purple-600 font-bold" id="duration-{{ $transaction->id }}">Calculating...</span>
                        </td>
                        <td class="p-3">
                            <span class="text-green-600 font-bold" id="amount-{{ $transaction->id }}">$0.00</span>
                        </td>
                        <td class="p-3">
                            <form action="{{ route('checkout', $transaction->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-600 transition"
                                        onclick="return confirm('Checkout this vehicle? Payment will be calculated.')">
                                    <i class="fas fa-receipt"></i> Checkout & Pay
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-parking text-gray-300 text-4xl mb-2"></i>
            <p class="text-gray-500">No vehicles currently parked</p>
        </div>
        @endif
    </div>

    <!-- Completed Transactions Section -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-check-circle mr-2"></i>Completed Payments
                    </h2>
                    <p class="text-gray-300 text-sm mt-1">History of all paid parking sessions</p>
                </div>
                <div class="text-right text-white text-sm">
                    <i class="fas fa-chart-line"></i> {{ $completedTransactions->total() }} records
                </div>
            </div>
        </div>

        @if($completedTransactions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">#</th>
                        <th class="p-3 text-left">Guest</th>
                        <th class="p-3 text-left">Vehicle Plate</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Slot</th>
                        <th class="p-3 text-left">Check-in</th>
                        <th class="p-3 text-left">Check-out</th>
                        <th class="p-3 text-left">Duration</th>
                        <th class="p-3 text-left">Amount</th>
                        <th class="p-3 text-left">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completedTransactions as $index => $transaction)
                    @php
                        $duration = '';
                        if ($transaction->check_in && $transaction->check_out) {
                            $minutes = $transaction->check_in->diffInMinutes($transaction->check_out);
                            $hours = floor($minutes / 60);
                            $mins = $minutes % 60;
                            $duration = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';
                        }
                    @endphp
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-500">{{ $completedTransactions->firstItem() + $index }}</td>
                        <td class="p-3 font-medium">{{ $transaction->guest_name }}</td>
                        <td class="p-3 font-mono">{{ $transaction->vehicle_plate }}</td>
                        <td class="p-3">
                            @if($transaction->user_type == 'pwd')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">
                                    <i class="fas fa-wheelchair"></i> PWD
                                </span>
                            @elseif($transaction->user_type == 'senior')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">
                                    <i class="fas fa-user-graduate"></i> Senior
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">Regular</span>
                            @endif
                        </td>
                        <td class="p-3 font-mono">{{ $transaction->parkingSlot->slot_number ?? 'N/A' }}</td>
                        <td class="p-3">{{ $transaction->check_in ? $transaction->check_in->format('h:i A') : '-' }}</td>
                        <td class="p-3">{{ $transaction->check_out ? $transaction->check_out->format('h:i A') : '-' }}<tr>
                        <td class="p-3">
                            <span class="font-mono text-purple-600">{{ $duration }}</span>
                        </td>
                        <td class="p-3">
                            <span class="text-green-600 font-bold text-lg">${{ number_format($transaction->amount, 2) }}</span>
                        </td>
                        <td class="p-3">
                            <button onclick="viewReceipt({{ $transaction->id }}, '{{ $transaction->guest_name }}', '{{ $transaction->vehicle_plate }}', '{{ $transaction->user_type }}', '{{ $transaction->check_in }}', '{{ $transaction->check_out }}', '{{ $transaction->amount }}', '{{ $duration }}')"
                                    class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">
                                <i class="fas fa-receipt"></i> View
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $completedTransactions->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-receipt text-gray-300 text-5xl mb-3"></i>
            <p class="text-gray-500 text-lg">No payment history yet</p>
            <p class="text-gray-400 text-sm">Completed transactions will appear here</p>
        </div>
        @endif
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 receipt-modal">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-t-2xl px-6 py-4">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">
                    <i class="fas fa-receipt mr-2"></i>Payment Receipt
                </h3>
                <button onclick="closeReceiptModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6" id="receiptContent">
            <!-- Dynamic receipt content -->
        </div>
        <div class="px-6 pb-6 flex gap-3">
            <button onclick="closeReceiptModal()" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-300 transition">
                Close
            </button>
            <button onclick="printReceipt()" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                <i class="fas fa-print mr-2"></i> Print
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Real-time duration updates for active vehicles (UPDATED: 1 minute free)
function updateActiveDurations() {
    @foreach($activeTransactions as $transaction)
    (function() {
        const checkinTime = new Date('{{ $transaction->check_in->toISOString() }}');
        const now = new Date();
        const minutes = Math.floor((now - checkinTime) / 60000);
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;

        // Update duration display
        const durationElem = document.getElementById('duration-{{ $transaction->id }}');
        if (durationElem) {
            durationElem.innerHTML = `${hours > 0 ? hours + 'h ' : ''}${mins}m`;
        }

        // Calculate and update estimated amount - UPDATED: 1 minute free (was 30)
        let amount = 0;
        if (minutes > 1) {
            let billableHours = Math.ceil((minutes - 1) / 60);
            amount = billableHours * 2;
            if ('{{ $transaction->user_type }}' === 'pwd' || '{{ $transaction->user_type }}' === 'senior') {
                amount = amount * 0.8;
            }
            amount = amount.toFixed(2);
        }

        const amountElem = document.getElementById('amount-{{ $transaction->id }}');
        if (amountElem) {
            amountElem.innerHTML = `$${amount}`;
            if (amount > 0) {
                amountElem.classList.add('text-green-600', 'font-bold');
            }
        }
    })();
    @endforeach
}

// Update every 30 seconds
setInterval(updateActiveDurations, 30000);
updateActiveDurations();

// View receipt for completed transaction
function viewReceipt(id, guestName, plate, userType, checkin, checkout, amount, duration) {
    const modal = document.getElementById('receiptModal');
    const content = document.getElementById('receiptContent');

    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkout);

    let discountText = '';
    if (userType === 'pwd' || userType === 'senior') {
        discountText = '<div class="flex justify-between text-sm mt-1"><span>Discount (20%):</span><span class="text-green-600">- Included</span></div>';
    }

    content.innerHTML = `
        <div class="text-center">
            <div class="bg-green-100 rounded-full p-3 inline-block mb-3">
                <i class="fas fa-check-circle text-green-600 text-3xl"></i>
            </div>
            <h3 class="font-bold text-xl mb-2">Payment Receipt</h3>
            <div class="bg-gray-50 rounded-lg p-4 text-left">
                <div class="border-b pb-2 mb-2">
                    <p><strong>Receipt #:</strong> ${id}</p>
                    <p><strong>Date:</strong> ${checkoutDate.toLocaleDateString()}</p>
                </div>
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span>Guest:</span>
                        <span class="font-semibold">${guestName}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Vehicle:</span>
                        <span class="font-mono">${plate}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Type:</span>
                        <span>${userType.toUpperCase()}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Check-in:</span>
                        <span class="font-mono">${checkinDate.toLocaleTimeString()}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Check-out:</span>
                        <span class="font-mono text-green-600">${checkoutDate.toLocaleTimeString()}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Duration:</span>
                        <span class="font-bold text-purple-600">${duration}</span>
                    </div>
                    ${discountText}
                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between">
                            <span class="text-lg font-bold">Amount Paid:</span>
                            <span class="text-2xl font-bold text-green-600">$${amount}</span>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3">First 1 minute free. Thank you for parking with us!</p>
        </div>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function printReceipt() {
    const content = document.getElementById('receiptContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Parking Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .receipt { max-width: 400px; margin: 0 auto; }
                    @media print {
                        button { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    ${content}
                </div>
                <script>window.print();<\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('receiptModal');
    if (event.target === modal) {
        closeReceiptModal();
    }
}

// Auto-show receipt modal after successful checkout
@if(session('receipt'))
document.addEventListener('DOMContentLoaded', function() {
    const receipt = @json(session('receipt'));

    Swal.fire({
        icon: 'success',
        title: '✅ Payment Successful!',
        html: `
            <div class="text-left">
                <div class="bg-gray-50 rounded-lg p-4 mb-3">
                    <p class="font-bold text-lg">${receipt.guest_name}</p>
                    <p class="text-gray-600">Vehicle: ${receipt.vehicle_plate}</p>
                    <p class="text-gray-600">Type: ${receipt.user_type.toUpperCase()}</p>
                    <p class="text-gray-600">Slot: ${receipt.slot_number} (Floor ${receipt.floor})</p>
                </div>
                <div class="border-t pt-3">
                    <div class="flex justify-between mb-1">
                        <span>Check-in:</span>
                        <span class="font-mono">${receipt.check_in_time}</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span>Check-out:</span>
                        <span class="font-mono text-green-600">${receipt.check_out_time}</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span>Duration:</span>
                        <span class="font-bold text-purple-600">${receipt.duration}</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span>Billable Hours:</span>
                        <span>${receipt.billable_hours} hour(s)</span>
                    </div>
                    ${receipt.discount_applied ? `
                    <div class="flex justify-between mb-1">
                        <span>Discount (20%):</span>
                        <span class="text-green-600">-$${receipt.discount_amount}</span>
                    </div>
                    ` : ''}
                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between">
                            <span class="text-lg font-bold">Total Amount:</span>
                            <span class="text-2xl font-bold text-green-600">$${receipt.amount}</span>
                        </div>
                    </div>
                    <div class="text-center text-xs text-gray-500 mt-3">
                        First 1 minute free | $2 per hour after
                    </div>
                </div>
            </div>
        `,
        confirmButtonText: 'OK',
        confirmButtonColor: '#22c55e'
    });
});
@endif

// Auto-hide success message after 5 seconds
setTimeout(function() {
    const successAlert = document.querySelector('.success-alert');
    if (successAlert) {
        successAlert.style.transition = 'opacity 0.5s';
        successAlert.style.opacity = '0';
        setTimeout(function() {
            successAlert.remove();
        }, 500);
    }
}, 5000);
</script>
@endsection
