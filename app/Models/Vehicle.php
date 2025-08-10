<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Vehicle extends Model
{
    protected $fillable = [
        'client_id',
        'brand',
        'model',
        'year',
        'plate'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }
}
