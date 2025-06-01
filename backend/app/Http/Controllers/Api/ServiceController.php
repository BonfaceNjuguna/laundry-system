<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Booking;

use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Service::all());
    }

    public function store(StoreServiceRequest $storeServiceRequest): JsonResponse
    {
        $service = Service::create($storeServiceRequest->validated());
        return response()->json($service, 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function update(StoreServiceRequest $storeServiceRequest, Service $service): JsonResponse
    {
        $service->update($storeServiceRequest->validated());
        return response()->json($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();
        return response()->json(null, 204);
    }
}
