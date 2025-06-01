<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;

use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Customer::all());
    }

    public function store(StoreCustomerRequest $storeCustomerRequest): JsonResponse
    {
        $customer = Customer::create($storeCustomerRequest->validated());
        return response()->json($customer, 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer);
    }

    public function update(StoreCustomerRequest $storeCustomerRequest, Customer $customer): JsonResponse
    {
        $customer->update($storeCustomerRequest->validated());
        return response()->json($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(null, 204);
    }
}
