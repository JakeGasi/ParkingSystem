<!DOCTYPE html>
<html>
<head>
    <title>Simple Checkout Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Checkout Test</h1>

    @php
        $activeVehicles = App\Models\ParkingTransaction::where('status', 'active')->get();
    @endphp

    @if($activeVehicles->count() > 0)
        <h3>Active Vehicles:</h3>
        <ul>
            @foreach($activeVehicles as $v)
            <li>
                ID: {{ $v->id }} - {{ $v->guest_name }} - {{ $v->vehicle_plate }}
                <button onclick="checkout({{ $v->id }})">Checkout</button>
            </li>
            @endforeach
        </ul>
    @else
        <p style="color: red;">No active vehicles! Please create a check-in first.</p>
        <a href="{{ url('/') }}">Go to Dashboard to create check-in</a>
    @endif

    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>

    <script>
    function checkout(id) {
        document.getElementById('result').innerHTML = 'Processing...';

        $.ajax({
            url: '/checkout/' + id,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT'
            },
            success: function(response) {
                document.getElementById('result').innerHTML = '<pre style="color: green;">SUCCESS: ' + JSON.stringify(response, null, 2) + '</pre>';
            },
            error: function(xhr) {
                document.getElementById('result').innerHTML = '<pre style="color: red;">ERROR: ' + xhr.responseText + '</pre>';
            }
        });
    }
    </script>
</body>
</html>
