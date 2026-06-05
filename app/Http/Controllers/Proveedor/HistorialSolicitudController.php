<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Services\HistorialSolicitudService;

class HistorialSolicitudController extends Controller
{
    public function __construct(
        protected HistorialSolicitudService $historialSolicitudService
    ) {
        $this->middleware('permission:ver solicitudes proveedor');
    }

    public function index()
    {
        $perfilProveedor = auth()->user()->perfilProveedor;

        return response()->json(
            $perfilProveedor
                ? $this->historialSolicitudService->historialProveedor($perfilProveedor)
                : collect()
        );
    }

    public function show(Solicitud $solicitud)
    {
        $this->authorize('view', $solicitud);

        return response()->json($this->historialSolicitudService->historialSolicitud($solicitud));
    }
}
