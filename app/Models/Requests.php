<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Requests extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'active_status',
        'request'
    ];
    public function users()
    {
        return $this->belongsTo(Requests::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
