<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Service;
use App\Models\Booking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $services = Service::all();

        foreach (range(1, 10) as $i) {
            $customer = $customers->random();
            $service = $services->random();
            $start = Carbon::now()->addDays(rand(1, 5));
            $end = (clone $start)->addHours(rand(2, 6));

            $booking = Booking::create([
            'customer_id' => $customer->id,
            'location' => 'Customer House ' . $i,
            'start_date' => $start,
            'end_date' => $end,
            'amount' => $service->price,
            'status' => 'pending',
            'payment_method' => 'mpesa',
            'is_paid' => rand(0, 1),
        ]);

        $booking->services()->attach($service->id);
        }
    }
}
