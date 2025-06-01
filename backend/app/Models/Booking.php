<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_id',
        'location',
        'start_date',
        'end_date',
        'amount',
        'status',
        'payment_method',
        'is_paid',
    ];
}
