<?php
// app/Services/NotaPdfService.php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class NotaPdfService
{
    public function buildPdf(int $id)
    {
        // Cambia 'logs' por el nombre real de tu tabla de notas si es distinto
        $log = DB::table('logs as l')
            ->leftJoin('vehicles as v', 'v.id', '=', 'l.vehicle_id')
            ->leftJoin('users as cli', 'cli.id', '=', 'l.client_id')
            ->leftJoin('users as mec', 'mec.id', '=', 'l.mechanic_id')
            ->where('l.id', $id)
            ->selectRaw("
                l.id,
                l.title,
                l.description,
                l.created_at,
                cli.name  as cliente,
                mec.name  as mecanico,
                CONCAT(COALESCE(v.brand,''),' ',COALESCE(v.model,''),' ',
                       COALESCE(CONCAT('(',v.year,')'),''), 
                       CASE WHEN v.plate IS NULL OR v.plate='' THEN '' ELSE CONCAT(' - ',v.plate) END
                ) as vehiculo
            ")
            ->first();

        abort_unless($log, 404, 'Nota no encontrada');

        // Si aún no tienes actividades/piezas, deja arrays vacíos
        $actividades   = collect();
        $piezas        = collect();
        $subtotalActiv = 0;
        $subtotalPzas  = 0;
        $total         = 0;

        $data = [
            'bitacora'      => (object)[
                'id'                 => $log->id,
                'fecha'              => $log->created_at ?? now(),
                'tipo_servicio'      => null, // si no aplicara
                'descripcion_general'=> $log->description,
                'estado_bitacora'    => 'registrada',
                'cliente'            => $log->cliente ?? 'Cliente',
                'mecanico'           => $log->mecanico ?? 'Mecánico',
                'vehiculo'           => $log->vehiculo ?? 'Vehículo',
            ],
            'actividades'   => $actividades,
            'piezas'        => $piezas,
            'subtotalActiv' => $subtotalActiv,
            'subtotalPzas'  => $subtotalPzas,
            'total'         => $total,
            'emitido'       => Carbon::now()->format('d/m/Y H:i'),
        ];

        return Pdf::loadView('pdf.nota', $data)->setPaper('a4','portrait');
    }
}
