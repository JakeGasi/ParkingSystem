<!DOCTYPE html>
<html>
<head>
    <title>Test Checkout</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .checkout-btn { background-color: #28a745; color: white; padding: 5px 10px; border: none; cursor: pointer; border-radius: 4px; }
        .checkout-btn:hover { background-color: #218838; }
    </style>
</head>
<body>
    <h1>Checkout Test Page</h1>

    @php
        $activeVehicles = App\Models\ParkingTransaction::with('parkingSlot')
            ->where('status', 'active')
            ->get();
    @endphp

    @if($activeVehicles->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guest Name</th>
                    <th>Vehicle Plate</th>
                    <th>User Type</th>
                    <th>Slot</th>
                    <th>Floor</th>
                    <th>Check-in Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeVehicles as $vehicle)
                <tr id="row-{{ $vehicle->id }}">
                    <td>{{ $vehicle->id }}</td>
                    <td>{{ $vehicle->guest_name }}</td>
                    <td>{{ $vehicle->vehicle_plate }}</td>
                    <td>{{ ucfirst($vehicle->user_type) }}</td>
                    <td>{{ $vehicle->parkingSlot->slot_number ?? 'N/A' }}</td>
                    <td>{{ $vehicle->parkingSlot->floor ?? 'N/A' }}</td>
                    <td>{{ $vehicle->check_in->format('h:i A') }}</td>
                    <td>
                        <button class="checkout-btn" onclick="checkout({{ $vehicle->id }})">Checkout & Pay</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: red;">No active vehicles. Please check in a vehicle first.</p>
        <a href="{{ url('/') }}">Go to Dashboard</a>
    @endif

    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; background: #f9f9f9;"></div>

    <script>
    function checkout(id) {
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = 'Processing checkout for ID: ' + id + '...';

        $.ajax({
            url: '/checkout/' + id,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT'
            },
            success: function(response) {
                if (response.success) {
                    resultDiv.innerHTML = `
                        <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; color: #155724;">
                            <h3>✅ Payment Successful!</h3>
                            <p><strong>Guest:</strong> ${response.guest_name}</p>
                            <p><strong>Vehicle:</strong> ${response.vehicle_plate}</p>
                            <p><strong>Location:</strong> Slot ${response.slot_number} (Floor ${response.floor})</p>
                            <p><strong>Check-in:</strong> ${response.check_in_time}</p>
                            <p><strong>Check-out:</strong> ${response.check_out_time}</p>
                            <p><strong>Duration:</strong> ${response.duration} (${response.minutes} minutes)</p>
                            <p><strong>Billable Hours:</strong> ${response.billable_hours}</p>
                            ${response.discount_applied ? '<p><strong>Discount:</strong> 20% applied for PWD/Senior</p>' : ''}
                            <p style="font-size: 24px; font-weight: bold; color: #28a745;">Amount: $${response.amount}</p>
                        </div>
                    `;
                    setTimeout(() => location.reload(), 3000);
                } else {
                    resultDiv.innerHTML = `<div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;">Error: ${response.error}</div>`;
                }
            },
            error: function(xhr) {
                let errorMsg = 'Checkout failed';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                resultDiv.innerHTML = `<div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;">Error: ${errorMsg}</div>`;
            }
        });
    }
    </script>
</body>
</html>
