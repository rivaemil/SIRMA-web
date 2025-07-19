<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function mechanic() { return $this->belongsTo(Mechanic::class); }
}
