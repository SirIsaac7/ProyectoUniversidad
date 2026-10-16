<?php

namespace App\Http\Controllers\Solicitudes\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Services\Solicitudes\HistorialSolicitudService;

class HistorialSolicitudController extends Controller
{
    public function __construct(
        protected HistorialSolicitudService $historialSolicitudService
    ) {
        $this->middleware('permission:ver solicitudes');
    }

    public function index()
    {
        return view('solicitudes.historial.admin.index');
    }

    public function show(Solicitud $solicitud)
    {
        $this->authorize('view', $solicitud);

        return response()->json($this->historialSolicitudService->historialSolicitud($solicitud));
    }
}
