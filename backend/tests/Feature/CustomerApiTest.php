<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Customer;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_customers(): void
    {
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_can_create_customer(): void
    {
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
        ];

        $response = $this->postJson('/api/customers', $data);

        $response->assertStatus(201)->assertJsonFragment($data);
        $this->assertDatabaseHas('customers', $data);
    }

    public function test_can_show_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)->assertJsonFragment([
            'id' => $customer->id,
        ]);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::factory()->create();

        $data = ['name' => 'Updated Name'];

        $response = $this->putJson("/api/customers/{$customer->id}", array_merge($customer->toArray(), $data));

        $response->assertStatus(200)->assertJsonFragment($data);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
