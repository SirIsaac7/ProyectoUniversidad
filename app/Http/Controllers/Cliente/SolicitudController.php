<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\StoreSolicitudRequest;
use App\Http\Requests\Cliente\UpdateSolicitudRequest;
use App\Models\Solicitud;
use App\Services\SolicitudService;

class SolicitudController extends Controller
{
    public function __construct(
        protected SolicitudService $solicitudService
    ) {
        $this->middleware('permission:ver mis solicitudes')->only('index');
        $this->middleware('permission:crear mis solicitudes')->only('store');
        $this->middleware('permission:editar mis solicitudes')->only('update');
        $this->middleware('permission:cancelar mis solicitudes')->only('destroy');
    }

    public function index()
    {
        return view('cliente.solicitudes.index', $this->solicitudService->datosFormularioCliente() + [
            'solicitudes' => $this->solicitudService->solicitudesCliente(auth()->user()),
            'resumenSolicitudes' => $this->solicitudService->resumenSolicitudesCliente(auth()->user()),
        ]);
    }

    public function store(StoreSolicitudRequest $request)
    {
        $this->solicitudService->createDesdeCliente(auth()->user(), $request->validated());

        return redirect()
            ->route('cliente.solicitudes.index')
            ->with('success', 'Solicitud creada correctamente.');
    }

    public function update(UpdateSolicitudRequest $request, Solicitud $solicitud)
    {
        $this->authorize('update', $solicitud);

        $this->solicitudService->updateDesdeCliente($solicitud, $request->validated());

        return redirect()
            ->route('cliente.solicitudes.index')
            ->with('success', 'Solicitud actualizada correctamente.');
    }

    public function destroy(Solicitud $solicitud)
    {
        $this->authorize('delete', $solicitud);

        $this->solicitudService->cancelar($solicitud);

        return redirect()
            ->route('cliente.solicitudes.index')
            ->with('success', 'Solicitud cancelada correctamente.');
    }
}
