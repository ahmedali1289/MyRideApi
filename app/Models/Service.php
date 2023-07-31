<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_fare',
        'per_min',
        'per_mile',
        'image',
    ];

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }
}
