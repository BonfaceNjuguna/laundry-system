<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_bookings(): void
    {
        Booking::factory()->count(5)->create();

        $response = $this->getJson('/api/bookings');

        $response->assertStatus(200)->assertJsonCount(5);
    }

    public function test_can_create_booking(): void
    {
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        $data = [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'location' => 'Nairobi',
            'start_date' => now()->addDay()->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
            'amount' => 1500.00,
            'status' => 'pending',
            'payment_method' => 'mpesa',
            'is_paid' => false,
        ];
        
        $response = $this->postJson('/api/bookings', $data);
        $response->assertStatus(201)->assertJsonFragment([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'location' => 'Nairobi',
            'amount' => 1500.00,
            'status' => 'pending',
            'payment_method' => 'mpesa',
            'is_paid' => false,
        ]);
        $this->assertDatabaseHas('bookings', $data);
    }

    public function test_can_show_booking(): void
    {
        $booking = Booking::factory()->create();

        $response = $this->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)->assertJsonFragment([
            'id' => $booking->id,
        ]);
    }

    public function test_can_update_booking(): void
    {
        $booking = Booking::factory()->create();

        $data = ['status' => 'confirmed'];

        $response = $this->putJson("/api/bookings/{$booking->id}", array_merge($booking->toArray(), $data));

        $response->assertStatus(200)->assertJsonFragment([
            'status' => 'confirmed',
        ]);
    }

    public function test_can_delete_booking(): void
    {
        $booking = Booking::factory()->create();

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }
}
