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
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_method' => 'nullable|in:mpesa,cash,card',
            'mpesa_transaction_id' => 'nullable|string|max:255',
            'is_paid' => 'boolean',
        ];
    }
}
