@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">

    <div class="bg-blue-600 rounded-xl shadow p-6 text-white mb-6">
        <h1 class="text-3xl font-bold">Edit Transaction #{{ $transaction->id }}</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ url('/transactions/' . $transaction->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block mb-2">Guest Name</label>
                <input type="text" name="guest_name" value="{{ $transaction->guest_name }}" class="w-full border p-3 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block mb-2">Vehicle Plate</label>
                <input type="text" name="vehicle_plate" value="{{ $transaction->vehicle_plate }}" class="w-full border p-3 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block mb-2">User Type</label>
                <select name="user_type" class="w-full border p-3 rounded">
                    <option value="regular" {{ $transaction->user_type == 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="pwd" {{ $transaction->user_type == 'pwd' ? 'selected' : '' }}>PWD</option>
                    <option value="senior" {{ $transaction->user_type == 'senior' ? 'selected' : '' }}>Senior</option>
                </select>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">Update</button>
            <a href="{{ url('/transactions') }}" class="bg-gray-300 px-6 py-3 rounded ml-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
