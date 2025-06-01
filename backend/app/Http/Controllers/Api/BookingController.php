<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;

use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Booking::all());
    }

    public function store(StoreBookingRequest $storeBookingRequest): JsonResponse
    {
        $booking = Booking::create($storeBookingRequest->validated());
        return response()->json($booking, 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking);
    }

    public function update(StoreBookingRequest $storeBookingRequest, Booking $booking): JsonResponse
    {
        $booking->update($storeBookingRequest->validated());
        return response()->json($booking);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();
        return response()->json(null, 204);
    }
}
