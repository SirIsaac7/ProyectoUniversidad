<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reportes\StoreReporteRequest;
use App\Http\Requests\Admin\Reportes\UpdateConfiguracionReporteRequest;
use App\Http\Requests\Admin\Reportes\UpdateReporteRequest;
use App\Models\Reporte;
use App\Services\ReporteService;
use RuntimeException;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteService $reporteService
    ) {
        $this->middleware('permission:ver reportes')->only('index');
        $this->middleware('permission:crear reportes')->only(['create', 'store']);
        $this->middleware('permission:editar reportes')->only(['edit', 'update']);
        $this->middleware('permission:eliminar reportes')->only('destroy');
        $this->middleware('permission:generar reportes')->only('pdf');
        $this->middleware('permission:configurar reportes')->only(['configuracion', 'actualizarConfiguracion']);
    }

    public function index()
    {
        return view('admin.reportes.index', [
            'reportes' => $this->reporteService->reportes(),
            'resumen' => $this->reporteService->resumenIndex(),
            'tipos' => $this->reporteService->tipos(),
        ]);
    }

    public function create()
    {
        return view('admin.reportes.partials.create', [
            'tipos' => $this->reporteService->tipos(),
            'opcionesPorTipo' => config('reportes.opciones', []),
        ]);
    }

    public function store(StoreReporteRequest $request)
    {
        $reporte = $this->reporteService->crear($request->validated());

        return redirect()
            ->route('reportes.edit', $reporte)
            ->with('success', 'Reporte creado correctamente.');
    }

    public function edit(Reporte $reporte)
    {
        return view('admin.reportes.partials.edit', [
            'reporte' => $reporte,
            'tipos' => $this->reporteService->tipos(),
            'opcionesPorTipo' => config('reportes.opciones', []),
        ]);
    }

    public function update(UpdateReporteRequest $request, Reporte $reporte)
    {
        $this->reporteService->actualizar($reporte, $request->validated());

        return redirect()
            ->route('reportes.index')
            ->with('success', 'Reporte actualizado correctamente.');
    }

    public function destroy(Reporte $reporte)
    {
        $this->reporteService->eliminar($reporte);

        return redirect()
            ->route('reportes.index')
            ->with('success', 'Reporte desactivado correctamente.');
    }

    public function pdf(Reporte $reporte)
    {
        try {
            return $this->reporteService->generarPdf($reporte);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function configuracion()
    {
        return view('admin.reportes.configuracion', [
            'configuracion' => $this->reporteService->configuracionPdf(),
            'tamanoHoja' => config('reportes.tamano_hoja', []),
            'orientaciones' => config('reportes.orientaciones', []),
        ]);
    }

    public function actualizarConfiguracion(UpdateConfiguracionReporteRequest $request)
    {
        $this->reporteService->actualizarConfiguracionPdf($request->validated());

        return redirect()
            ->route('reportes.configuracion')
            ->with('success', 'Configuracion general de reportes actualizada correctamente.');
    }
}
