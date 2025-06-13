<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use Illuminate\Http\Request;
use App\Models\Expense;

use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with('booking');

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }

        if ($request->filled('service_id')) {
            $query->whereHas('booking.services', fn ($q) => $q->where('services.id', $request->service_id));
        }

        if ($request->filled('customer_id')) {
            $query->whereHas('booking', fn ($q) => $q->where('customer_id', $request->customer_id));
        }

        return response()->json($query->get());
    }

    public function store(StoreExpenseRequest $storeExpenseRequest): JsonResponse
    {
        try {
            $validatedData = $storeExpenseRequest->validated();
            $expense = Expense::create($validatedData);
            return response()->json($expense, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save expense',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json($expense);
    }

    public function update(StoreExpenseRequest $storeExpenseRequest, Expense $expense): JsonResponse
    {
        $validatedData = $storeExpenseRequest->validated();
        $expense->update($validatedData);
        return response()->json($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();
        return response()->json(null, 204);
    }
}
