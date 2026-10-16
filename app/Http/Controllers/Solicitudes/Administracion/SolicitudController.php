<?php

namespace App\Http\Controllers\Solicitudes\Administracion;

use App\Http\Controllers\Controller;

use App\Models\Solicitud;
use App\Services\Solicitudes\SolicitudService;

class SolicitudController extends Controller
{
    public function __construct(
        protected SolicitudService $solicitudService
    ) {
        $this->middleware('permission:ver solicitudes')->only('index');
        $this->middleware('permission:eliminar solicitudes')->only('destroy');
    }

    public function index()
    {
        return view('solicitudes.admin.index');
    }

    public function destroy(Solicitud $solicitude)
    {
        $this->solicitudService->cancelar($solicitude);

        $mensaje = $solicitude->estado === 'cancelada'
            ? 'Solicitud cancelada correctamente.'
            : 'Solicitud actualizada correctamente.';

        return redirect()
            ->route('solicitudes.index')
            ->with('success', $mensaje);
    }
}
