<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'location' => $this->faker->address,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'amount' => $this->faker->randomFloat(2, 500, 5000),
            'status' => 'pending',
            'payment_method' => 'mpesa',
            'is_paid' => false,
        ];
    }
}
