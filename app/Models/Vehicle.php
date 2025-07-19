<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    public function client() { return $this->belongsTo(Client::class); }
    public function logs() { return $this->hasMany(Log::class); }
}
