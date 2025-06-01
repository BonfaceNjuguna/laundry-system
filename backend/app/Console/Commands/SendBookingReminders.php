<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Mail\BookingReminderMail;
use Illuminate\Support\Facades\Mail;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = 'Send reminder and follow-up emails for bookings';

    public function handle(): void
    {
        $now = now();
        $admins = ['bonfacenjuguna13@gmail.com'];

        $bookings = Booking::with(['customer', 'service'])
            ->where(function ($query) use ($now) {
                $query
                    ->where(function ($q) use ($now) {
                        $q->where('status', 'pending')
                          ->where('start_date', '<=', $now->copy()->addDay())
                          ->where('start_date', '>=', $now);
                    })
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending')
                          ->where('is_paid', false);
                    })
                    ->orWhere(function ($q) use ($now) {
                        $q->where('status', 'pending')
                          ->where('end_date', '<', $now);
                    });
            })
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings found for reminders.');
            return;
        }

        foreach ($bookings as $booking) {
            foreach ($admins as $adminEmail) {
                Mail::to($adminEmail)->send(new BookingReminderMail($booking));
            }
        }

        $this->info("Sent reminders for {$bookings->count()} booking(s).");
    }
}
