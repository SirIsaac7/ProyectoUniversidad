<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Services\HistorialSolicitudService;

class HistorialSolicitudController extends Controller
{
    public function __construct(
        protected HistorialSolicitudService $historialSolicitudService
    ) {
        $this->middleware('permission:ver mis solicitudes');
    }

    public function index()
    {
        return response()->json($this->historialSolicitudService->historialCliente(auth()->user()));
    }

    public function show(Solicitud $solicitud)
    {
        $this->authorize('view', $solicitud);

        return response()->json($this->historialSolicitudService->historialSolicitud($solicitud));
    }
}
