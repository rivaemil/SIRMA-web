<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Appointment;
use App\Models\Log;


class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function vehicles() { return $this->hasMany(Vehicle::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function logs() { return $this->hasMany(Log::class); }
}
