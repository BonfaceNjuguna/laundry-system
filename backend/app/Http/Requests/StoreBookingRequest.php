<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.amount' => 'required|numeric|min:0',
            'expenses' => 'nullable|array',
            'expenses.*.category' => 'required|string|max:255',
            'expenses.*.amount' => 'required|numeric|min:0',
            'expenses.*.description' => 'nullable|string|max:1000',
            'expenses.*.date' => 'nullable|date',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_method' => 'nullable|in:mpesa,cash,card',
            'mpesa_transaction_id' => 'nullable|string|max:255',
            'is_paid' => 'boolean',
        ];
    }
}
