<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'title',
        'vehicle_id',
        'client_id',
        'mechanic_id',
        'description'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class);
    }
}
