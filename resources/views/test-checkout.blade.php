<!DOCTYPE html>
<html>
<head>
    <title>Test Checkout</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="padding: 20px; font-family: Arial;">
    <h1>Checkout Test Page</h1>

    @php
        $activeVehicles = App\Models\ParkingTransaction::where('status', 'active')->get();
    @endphp

    @if($activeVehicles->count() > 0)
        <h2>Active Vehicles ({{ $activeVehicles->count() }})</h2>
        <table border="1" cellpadding="10">
            <tr>
                <th>ID</th><th>Guest</th><th>Plate</th><th>Slot</th><th>Action</th>
            </tr>
            @foreach($activeVehicles as $vehicle)
            <tr>
                <td>{{ $vehicle->id }}</td>
                <td>{{ $vehicle->guest_name }}</td>
                <td>{{ $vehicle->vehicle_plate }}</td>
                <td>{{ $vehicle->parkingSlot->slot_number ?? 'N/A' }}</td>
                <td>
                    <button onclick="testCheckout({{ $vehicle->id }})">Test Checkout</button>
                </td>
            </tr>
            @endforeach
        </table>
    @else
        <p style="color: red;">No active vehicles found. Please create a check-in first.</p>
        <a href="{{ url('/') }}">Go to Dashboard to create check-in</a>
    @endif

    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; background: #f9f9f9;"></div>

    <script>
    function testCheckout(id) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('result').innerHTML = 'Processing checkout for ID: ' + id + '...';

        $.ajax({
            url: '/checkout/' + id,
            type: 'POST',
            data: {
                _token: token,
                _method: 'PUT'
            },
            success: function(response) {
                document.getElementById('result').innerHTML = '<pre style="color: green; background: #e8f5e9; padding: 10px;">SUCCESS:\n' + JSON.stringify(response, null, 2) + '</pre>';
            },
            error: function(xhr) {
                document.getElementById('result').innerHTML = '<pre style="color: red; background: #ffebee; padding: 10px;">ERROR:\nStatus: ' + xhr.status + '\nResponse: ' + xhr.responseText + '</pre>';
            }
        });
    }
    </script>
</body>
</html>
