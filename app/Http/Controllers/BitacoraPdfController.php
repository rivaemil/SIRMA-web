<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotaPdfService; // servicio opcional para no duplicar lógica

class BitacoraPdfController extends Controller
{
    public function __construct(private NotaPdfService $svc) {}

    // Ruta WEB: descarga/visualización directa en navegador
    public function downloadWeb(Request $request, int $id)
    {
        // Si tu app usa policies/sesión tradicional, aquí podrías autorizar:
        // $this->authorize('viewBitacora', $id);

        $pdf = $this->svc->buildPdf($id);   // DomPDF instance
        // Abre en el visor del navegador:
        return $pdf->stream("bitacora_{$id}.pdf");
        // Si quieres forzar descarga:
        // return $pdf->download("bitacora_{$id}.pdf");
    }
}
