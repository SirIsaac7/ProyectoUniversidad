<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedor\UpdateSolicitudRequest;
use App\Models\Solicitud;
use App\Services\SolicitudService;

class SolicitudController extends Controller
{
    public function __construct(
        protected SolicitudService $solicitudService
    ) {
        $this->middleware('permission:ver solicitudes proveedor')->only('index');
        $this->middleware('permission:gestionar solicitudes proveedor')->only('cambiarEstado');
    }

    public function index()
    {
        $perfilProveedor = auth()->user()->perfilProveedor;

        return view('proveedor.solicitudes.index', [
            'perfilProveedor' => $perfilProveedor,
            'solicitudes' => $perfilProveedor
                ? $this->solicitudService->solicitudesProveedor($perfilProveedor)
                : collect(),
        ]);
    }

    public function cambiarEstado(UpdateSolicitudRequest $request, Solicitud $solicitud)
    {
        $this->authorize('gestionarProveedor', $solicitud);

        $this->solicitudService->cambiarEstadoDesdeProveedor(
            $solicitud,
            $request->validated('estado'),
            $request->validated('comentario')
        );

        return redirect()
            ->route('proveedor.solicitudes.index')
            ->with('success', 'Solicitud actualizada correctamente.');
    }
}
