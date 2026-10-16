<?php

namespace App\Http\Controllers\Solicitudes\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Services\Solicitudes\HistorialSolicitudService;

class HistorialSolicitudController extends Controller
{
    public function __construct(
        protected HistorialSolicitudService $historialSolicitudService
    ) {
        $this->middleware('permission:ver mis solicitudes proveedor');
    }

    public function index()
    {
        return redirect()->route('proveedor.solicitudes.index', ['tab' => 'historial']);
    }

    public function show(Solicitud $solicitud)
    {
        $this->authorize('view', $solicitud);

        return response()->json($this->historialSolicitudService->historialSolicitud($solicitud));
    }
}
