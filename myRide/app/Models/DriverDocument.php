<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'city',
        'state',
        'car_make',
        'car_model',
        'car_year',
        'car_color',
        'car_capacity',
        'service',
        'driver_liscence',
        'car_registration',
        'car_insurance',
        'liscence_picture',
        'car_picture',
    ];

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'document_id');
    }
    public function rides()
    {
        return $this->hasMany(Ride::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
