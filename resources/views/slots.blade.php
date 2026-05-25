@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-lg shadow p-6 text-white mb-6">
        <h1 class="text-3xl font-bold">Parking Slots Management</h1>
        <p>Manage all 50 parking slots</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-2">
            @foreach($slots ?? [] as $slot)
            <div class="border-2 rounded p-2 text-center text-xs
                {{ $slot->status == 'occupied' ? 'bg-red-100 border-red-400' : 'bg-green-100 border-green-400' }}">
                <i class="fas fa-{{ $slot->status == 'occupied' ? 'car' : 'parking' }} text-lg"></i>
                <p class="font-bold">{{ $slot->slot_number }}</p>
                <p class="text-gray-600">{{ $slot->floor }}</p>
                <p class="text-xs mt-1">{{ $slot->type == 'pwd' ? 'PWD' : ($slot->type == 'senior' ? 'Senior' : 'Regular') }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
