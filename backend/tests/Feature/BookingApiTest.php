<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user and authenticate
        Sanctum::actingAs(User::factory()->create());
    }

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
            'services' => [
                ['service_id' => $service->id, 'amount' => 1500.00]
            ],
            'location' => 'Nairobi',
            'start_date' => now()->addDay()->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
            'status' => 'pending',
            'payment_method' => 'mpesa',
            'is_paid' => false,
            'expenses' => [
                ['category' => 'Transport', 'amount' => 100, 'description' => 'Taxi', 'date' => now()->toDateTimeString()]
            ]
        ];
        
        $response = $this->postJson('/api/bookings', $data);
        $response->assertStatus(201)->assertJsonFragment([
            'customer_id' => $customer->id,
            'location' => 'Nairobi',
            'status' => 'pending',
            'payment_method' => 'mpesa',
            'is_paid' => false,
        ]);
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'location' => 'Nairobi',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'pending',
            'payment_method' => 'mpesa',
            'is_paid' => false,
        ]);

        $this->assertDatabaseHas('booking_service', [
            'booking_id' => Booking::first()->id,
            'service_id' => $service->id,
            'amount' => 1500.00,
        ]);
        $this->assertDatabaseHas('expenses', [
            'booking_id' => Booking::first()->id,
            'category' => 'Transport',
            'amount' => 100,
        ]);
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
        $customer = Customer::factory()->create();
        $service = Service::factory()->create();

        // Create the booking
        $booking = Booking::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $booking->services()->sync([$service->id]);

        // Prepare full update data
        $data = [
            'customer_id' => $customer->id,
            'services' => [
                ['service_id' => $service->id, 'amount' => 2500.00]
            ],
            'location' => 'Updated Location',
            'start_date' => now()->addDays(1)->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
            'status' => 'confirmed',
            'payment_method' => 'mpesa',
            'is_paid' => true,
            // 'service_ids' => [$service->id], // <-- REMOVE THIS LINE
            'expenses' => [
                ['category' => 'Transport', 'amount' => 100, 'description' => 'Taxi', 'date' => now()->toDateTimeString()]
            ]
        ];

        $response = $this->putJson("/api/bookings/{$booking->id}", $data);

        $response->assertStatus(200)->assertJsonFragment([
            'customer_id' => $customer->id,
            'location' => 'Updated Location',
            'status' => 'confirmed',
            'payment_method' => 'mpesa',
            'is_paid' => true,
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'customer_id' => $customer->id,
            'location' => 'Updated Location',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'confirmed',
            'payment_method' => 'mpesa',
            'is_paid' => true,
        ]);
        $this->assertDatabaseHas('booking_service', [
            'booking_id' => $booking->id,
            'service_id' => $service->id,
            'amount' => 2500.00,
        ]);
        $this->assertDatabaseHas('expenses', [
            'booking_id' => $booking->id,
            'category' => 'Transport',
            'amount' => 100,
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
