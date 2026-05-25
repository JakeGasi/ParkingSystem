@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4"><i class="fas fa-parking"></i> Manage Parking Slots</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($slots as $slot)
        <div class="border rounded p-3">
            <div class="font-bold">{{ $slot->slot_number }}</div>
            <div class="text-sm">Floor: {{ $slot->floor }}</div>
            <select onchange="updateSlotType({{ $slot->id }}, this.value)" class="mt-2 text-sm border rounded p-1 w-full">
                <option value="regular" {{ $slot->type == 'regular' ? 'selected' : '' }}>Regular</option>
                <option value="pwd" {{ $slot->type == 'pwd' ? 'selected' : '' }}>PWD</option>
                <option value="senior" {{ $slot->type == 'senior' ? 'selected' : '' }}>Senior</option>
            </select>
            <button onclick="deleteSlot({{ $slot->id }})" class="mt-2 text-red-600 text-sm w-full" {{ $slot->status == 'occupied' ? 'disabled' : '' }}>
                Delete
            </button>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function updateSlotType(id, type) {
    $.ajax({
        url: '{{ url("/slots") }}/' + id,
        type: 'PUT',
        data: { type: type, _token: '{{ csrf_token() }}' },
        success: function() {
            Swal.fire('Updated!', 'Slot type updated', 'success');
        }
    });
}

function deleteSlot(id) {
    Swal.fire({
        title: 'Delete slot?',
        text: "Cannot be undone",
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("/slots") }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    location.reload();
                },
                error: function() {
                    Swal.fire('Error', 'Cannot delete occupied slot', 'error');
                }
            });
        }
    });
}
</script>
@endpush
@endsection
