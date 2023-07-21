<?php

namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class DriverDocument extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

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
}
