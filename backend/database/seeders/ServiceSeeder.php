<?php

namespace Database\Seeders;

use App\Models\Service;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Carpet Cleaning', 'description' => 'Deep carpet wash', 'price' => 1500],
            ['name' => 'House Cleaning', 'description' => 'Full house sanitization', 'price' => 3000],
            ['name' => 'Fumigation', 'description' => 'Pest control service', 'price' => 2500],
            ['name' => 'Sofa Set Cleaning', 'description' => 'Cleaning of all types of sofas', 'price' => 2000],
            ['name' => 'Mattress Cleaning', 'description' => 'Deep clean for mattress', 'price' => 1000],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
