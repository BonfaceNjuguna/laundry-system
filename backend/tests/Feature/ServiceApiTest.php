<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Service;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_services(): void
    {
        Service::factory()->count(5)->create();

        $response = $this->getJson('/api/services');

        $response->assertStatus(200)->assertJsonCount(5);
    }

    public function test_can_create_service(): void
    {
        $data = [
            'name' => 'Cleaning Service',
            'description' => 'Full house cleaning',
            'price' => 100.00,
        ];

        $response = $this->postJson('/api/services', $data);

        $response->assertStatus(201)->assertJsonFragment($data);
        $this->assertDatabaseHas('services', $data);
    }

    public function test_can_show_service(): void
    {
        $service = Service::factory()->create();

        $response = $this->getJson("/api/services/{$service->id}");

        $response->assertStatus(200)->assertJsonFragment([
            'id' => $service->id,
        ]);
    }

    public function test_can_update_service(): void
    {
        $service = \App\Models\Service::factory()->create();

        $data = ['name' => 'Fumigation Service'];

        $response = $this->putJson("/api/services/{$service->id}", array_merge($service->toArray(), $data));

        $response->assertStatus(200)->assertJsonFragment($data);
    }

    public function test_can_delete_service(): void
    {
        $service = Service::factory()->create();

        $response = $this->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }
}
