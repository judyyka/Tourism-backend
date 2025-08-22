@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10">
    <h2 class="text-2xl font-bold text-blue-600 mb-4">Trips Dashboard</h2>

    @foreach($trips as $trip)
        <div class="border border-blue-200 rounded-xl p-4 mb-6 shadow-md">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-blue-600">{{ $trip->name }}</h3>
                <div>
                    <a href="{{ route('trips.edit', $trip->id) }}" class="bg-yellow-500 text-white px-4 py-1 rounded">Edit</a>
                    <form action="{{ route('trips.destroy', $trip->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="bg-red-600 text-white px-4 py-1 rounded" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </div>
            </div>

            <div class="mt-2">
                <p class="text-gray-700">{{ $trip->description }}</p>
                <p class="text-gray-600">Price: ${{ $trip->price }}, Days: {{ $trip->count_days }}, Start: {{ $trip->start_date }}</p>
                <p class="text-gray-600">Hotel: {{ $trip->hotel->name ?? '-' }}, Room: {{ $trip->room->room_type ?? '-' }}</p>
                <p class="text-gray-600">Category: {{ $trip->category->name ?? '-' }}, Governorate: {{ $trip->governorate->name ?? '-' }}</p>
                <p class="text-gray-600">Guide: {{ $trip->guide->name ?? '-' }}, Transport: {{ $trip->transportation->name ?? '-' }}</p>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2">
                @foreach($trip->images as $img)
                    <img src="{{ asset('storage/trips/'.$img->image) }}" class="w-full h-32 object-cover rounded">
                @endforeach
            </div>

            <div class="mt-4">
                <h4 class="text-blue-600 font-semibold">Days & Activities</h4>
                @foreach($trip->days as $day)
                    <div class="border border-blue-100 rounded p-2 mt-2">
                        <p class="font-bold">{{ $day->name }} ({{ $day->date }})</p>

                        @if($day->activities->count())
                            <p>Activities: {{ $day->activities->pluck('name')->join(', ') }}</p>
                        @endif
                        @if($day->restaurants->count())
                            <p>Restaurants: {{ $day->restaurants->pluck('name')->join(', ') }}</p>
                        @endif
                        @if($day->places->count())
                            <p>Places: {{ $day->places->pluck('name')->join(', ') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
