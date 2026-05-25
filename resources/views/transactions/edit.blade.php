@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-md mx-auto">
    <h2 class="text-xl font-bold mb-4">Edit Transaction #{{ $transaction->id }}</h2>

    <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Guest Name</label>
            <input type="text" name="guest_name" value="{{ $transaction->guest_name }}" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Vehicle Plate</label>
            <input type="text" name="vehicle_plate" value="{{ $transaction->vehicle_plate }}" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1">User Type</label>
            <select name="user_type" class="w-full border rounded p-2">
                <option value="regular" {{ $transaction->user_type == 'regular' ? 'selected' : '' }}>Regular</option>
                <option value="pwd" {{ $transaction->user_type == 'pwd' ? 'selected' : '' }}>PWD</option>
                <option value="senior" {{ $transaction->user_type == 'senior' ? 'selected' : '' }}>Senior</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
        <a href="{{ route('transactions') }}" class="ml-2 text-gray-600">Cancel</a>
    </form>
</div>
@endsection
