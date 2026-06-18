<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedor\UpdateSolicitudRequest;
use App\Models\Solicitud;
use App\Services\CitaService;
use App\Services\HistorialSolicitudService;
use App\Services\Proveedor\SolicitudProveedorService;
use App\Services\SolicitudService;

class SolicitudController extends Controller
{
    public function __construct(
        protected SolicitudService $solicitudService,
        protected SolicitudProveedorService $solicitudProveedorService,
        protected CitaService $citaService,
        protected HistorialSolicitudService $historialSolicitudService
    ) {
        $this->middleware('permission:ver mis solicitudes proveedor')->only('index');
        $this->middleware('permission:editar mis solicitudes proveedor|cancelar mis solicitudes proveedor')->only('cambiarEstado');
    }

    public function index()
    {
        $perfilProveedor = auth()->user()->perfilProveedor;
        $estadoMeta = $this->solicitudProveedorService->estadosVista();
        $estadoCitaMeta = $this->citaService->estadosVistaCliente();
        $solicitudes = $perfilProveedor
            ? $this->solicitudProveedorService->solicitudes($perfilProveedor)
            : collect();
        $citas = $perfilProveedor && auth()->user()->can('ver mis citas')
            ? $this->citaService->citasProveedor($perfilProveedor)
            : collect();
        $historiales = $perfilProveedor
            ? $this->historialSolicitudService->historialProveedor($perfilProveedor)
            : collect();
        $resumenSolicitudes = $perfilProveedor
            ? $this->solicitudProveedorService->resumenSolicitudes($perfilProveedor)
            : [
                'pendientes' => 0,
                'aceptadas' => 0,
                'rechazadas' => 0,
                'finalizadas' => 0,
            ];
        $solicitudInicial = method_exists($solicitudes, 'getCollection')
            ? $solicitudes->getCollection()->first()
            : null;

        return view('proveedor.solicitudes.index', [
            'tabActivo' => request('tab', 'solicitudes'),
            'perfilProveedor' => $perfilProveedor,
            'solicitudes' => $solicitudes,
            'solicitudesVista' => method_exists($solicitudes, 'getCollection')
                ? $this->solicitudProveedorService->solicitudesVista($solicitudes, $estadoMeta)
                : collect(),
            'solicitudInicialDetalle' => $this->solicitudProveedorService->detalleVista($solicitudInicial, $estadoMeta),
            'resumenSolicitudes' => $resumenSolicitudes,
            'estadisticas' => $this->solicitudProveedorService->estadisticasVista($resumenSolicitudes),
            'estadoMeta' => $estadoMeta,
            'citas' => $citas,
            'citasVista' => method_exists($citas, 'getCollection')
                ? $this->citaService->citasVistaProveedor($citas, $estadoCitaMeta)
                : collect(),
            'historiales' => $historiales,
            'historialesVista' => method_exists($historiales, 'getCollection')
                ? $this->historialSolicitudService->historialVistaProveedor($historiales)
                : collect(),
        ]);
    }

    public function cambiarEstado(UpdateSolicitudRequest $request, Solicitud $solicitud)
    {
        $this->authorize('gestionarProveedor', $solicitud);
        $estado = $request->validated('estado');

        if ($estado === 'rechazada') {
            abort_unless(auth()->user()->can('cancelar mis solicitudes proveedor'), 403);
        } else {
            abort_unless(auth()->user()->can('editar mis solicitudes proveedor'), 403);
        }

        $this->solicitudService->cambiarEstadoDesdeProveedor(
            $solicitud,
            $estado,
            $request->validated('comentario')
        );

        return redirect()
            ->route('proveedor.solicitudes.index')
            ->with('success', 'Solicitud actualizada correctamente.');
    }
}
