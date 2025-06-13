<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Expense;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'location',
        'start_date',
        'end_date',
        'amount',
        'status',
        'payment_method',
        'mpesa_transaction_id',
        'is_paid',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_service')->withPivot('amount');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
