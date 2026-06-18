<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\StoreSolicitudRequest;
use App\Models\Solicitud;
use App\Services\CitaService;
use App\Services\HistorialSolicitudService;
use App\Services\SolicitudService;

class SolicitudController extends Controller
{
    public function __construct(
        protected SolicitudService $solicitudService,
        protected CitaService $citaService,
        protected HistorialSolicitudService $historialSolicitudService
    ) {
        $this->middleware('permission:ver mis solicitudes')->only('index');
        $this->middleware('permission:crear mis solicitudes')->only('store');
        $this->middleware('permission:cancelar mis solicitudes')->only('destroy');
    }

    public function index()
    {
        $solicitudes = $this->solicitudService->solicitudesCliente(auth()->user());
        $resumenSolicitudes = $this->solicitudService->resumenSolicitudesCliente(auth()->user());
        $estadoMeta = $this->solicitudService->estadosVistaCliente();
        $estadoCitaMeta = $this->citaService->estadosVistaCliente();
        $citas = auth()->user()->can('ver mis citas')
            ? $this->citaService->citasCliente(auth()->user())
            : collect();
        $historiales = $this->historialSolicitudService->historialCliente(auth()->user());
        $solicitudInicial = $solicitudes->getCollection()->first();

        return view('cliente.solicitudes.index', $this->solicitudService->datosFormularioCliente() + [
            'tabActivo' => request('tab', 'solicitudes'),
            'solicitudes' => $solicitudes,
            'solicitudesVista' => $this->solicitudService->solicitudesVistaCliente($solicitudes, $estadoMeta),
            'solicitudInicial' => $solicitudInicial,
            'solicitudInicialDetalle' => $this->solicitudService->detalleVistaCliente($solicitudInicial, $estadoMeta),
            'mostrarPanel' => request()->filled('perfil_proveedor_id') || session()->has('errors'),
            'proveedorSeleccionadoId' => request('perfil_proveedor_id'),
            'especialidadSeleccionadaId' => request('especialidad_id'),
            'resumenSolicitudes' => $resumenSolicitudes,
            'estadisticas' => $this->solicitudService->estadisticasVistaCliente($resumenSolicitudes),
            'estadoMeta' => $estadoMeta,
            'citas' => $citas,
            'citasVista' => method_exists($citas, 'getCollection')
                ? $this->citaService->citasVistaCliente($citas, $estadoCitaMeta)
                : collect(),
            'historiales' => $historiales,
            'historialesVista' => $this->historialSolicitudService->historialVistaCliente($historiales),
        ]);
    }

    public function store(StoreSolicitudRequest $request)
    {
        $this->solicitudService->createDesdeCliente(auth()->user(), $request->validated());

        if ($request->input('origen') === 'busqueda_servicios') {
            return redirect()
                ->back()
                ->with('success', 'Solicitud creada correctamente.');
        }

        return redirect()
            ->route('cliente.solicitudes.index')
            ->with('success', 'Solicitud creada correctamente.');
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
