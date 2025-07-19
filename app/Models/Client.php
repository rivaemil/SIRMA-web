<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public function user() { return $this->belongsTo(User::class); }
    public function vehicles() { return $this->hasMany(Vehicle::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function logs() { return $this->hasMany(Log::class); }
}
