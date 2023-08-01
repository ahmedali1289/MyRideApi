<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'pick_up',
        'drop_of',
        'no_of_passenger',
        'distance',
        'estimated_time',
        'estimated_fare',
        'service_id',
        'card_id',
        'accept_driver_id',
        'card_id',
        'accept_time',
        'code', 
        'user_id',        
        'status',        
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
