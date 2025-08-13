<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    // Lista de atributos que pueden asignarse masivamente (mass assignment)
    protected $fillable = [
         // Título del registro (log)
        'title',      
        // ID del vehículo asociado
        'vehicle_id',  
        // ID del cliente asociado
        'client_id',
        // ID del mecánico asignado   
        'mechanic_id', 
        // Descripción detallada del registro
        'description'  
    ];

    // Relación: un Log pertenece a un vehículo
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Relación: un Log pertenece a un cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación: un Log pertenece a un mecánico
    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class);
    }
}
