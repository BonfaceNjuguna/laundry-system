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
        $bookings = Booking::with(['customer', 'services'])->get();
        return response()->json($bookings);
    }

    public function store(StoreBookingRequest $storeBookingRequest): JsonResponse
    {
        try {
            $data = $storeBookingRequest->validated();
            $serviceIds = $data['service_ids'];
            unset($data['service_ids']);

            $booking = Booking::create($data);
            $booking->services()->sync($serviceIds);

            return response()->json($booking->load('services'), 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save booking',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking);
    }

    public function update(StoreBookingRequest $storeBookingRequest, Booking $booking): JsonResponse
    {
        $data = $storeBookingRequest->validated();
        $serviceIds = $data['service_ids'];
        unset($data['service_ids']);

        $booking->update($data);
        $booking->services()->sync($serviceIds);

        return response()->json($booking->load('services'));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();
        return response()->json(null, 204);
    }
}
