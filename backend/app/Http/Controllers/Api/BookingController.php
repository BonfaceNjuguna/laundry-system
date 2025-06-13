<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Service;

use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['customer', 'services', 'expenses']);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('start_date', [$request->from, $request->to]);
        }

        if ($request->filled('service_id')) {
            $query->whereHas('services', fn ($q) => $q->where('services.id', $request->service_id));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $bookings = $query->get();

        return response()->json($bookings);
    }


    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Calculate total amount from services
            $totalAmount = collect($data['services'])->sum('amount');
            $data['amount'] = $totalAmount;

            $services = $data['services'];
            unset($data['services']);
            $expenses = $data['expenses'] ?? [];
            unset($data['expenses']);

            $booking = Booking::create($data);

            // Attach services with pivot data (amount)
            $serviceSync = [];
            foreach ($services as $service) {
                $serviceSync[$service['service_id']] = ['amount' => $service['amount']];
            }
            $booking->services()->sync($serviceSync);

            // Create expenses
            foreach ($expenses as $expense) {
                $expense['booking_id'] = $booking->id;
                if (!isset($expense['date'])) {
                    $expense['date'] = now(); // Default to current date if not provided
                }
                unset($expense['id']); // Ensure no ID is passed to create a new expense
                $booking->expenses()->create($expense);
            }

            return response()->json($booking->load(['services', 'expenses']), 201);
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
        return response()->json($booking->load(['services', 'expenses']));
    }

    public function update(StoreBookingRequest $request, Booking $booking): JsonResponse
    {
        $data = $request->validated();

        $totalAmount = collect($data['services'])->sum('amount');
        $data['amount'] = $totalAmount;

        $services = $data['services'];
        unset($data['services']);
        $expenses = $data['expenses'] ?? [];
        unset($data['expenses']);

        $booking->update($data);

        // Sync services with amounts
        $serviceSync = [];
        foreach ($services as $service) {
            $serviceSync[$service['service_id']] = ['amount' => $service['amount']];
        }
        $booking->services()->sync($serviceSync);

        // Remove old expenses and add new ones
        $booking->expenses()->delete();
        foreach ($expenses as $expense) {
            $expense['booking_id'] = $booking->id;
            if (!isset($expense['date'])) {
                $expense['date'] = now(); // Default to current date if not provided
            }
            unset($expense['id']); // Ensure no ID is passed to create a new expense
            $booking->expenses()->create($expense);
        }

        return response()->json($booking->load(['services', 'expenses']));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();
        return response()->json(null, 204);
    }
}
