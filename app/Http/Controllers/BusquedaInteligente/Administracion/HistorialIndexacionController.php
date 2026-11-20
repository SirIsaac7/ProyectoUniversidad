<?php

namespace App\Http\Controllers\BusquedaInteligente\Administracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusquedaInteligente\Administracion\FiltrarHistorialIndexacionRequest;
use App\Models\EjecucionIndexacionMotor;
use App\Services\BusquedaInteligente\Administracion\MotorIndexacionService;

class HistorialIndexacionController extends Controller
{
    public function __construct(
        protected MotorIndexacionService $motorIndexacionService,
    ) {
        $this->middleware('permission:ver panel motor busqueda')->only(['index', 'show']);
        $this->middleware('permission:ver historial indexaciones motor')->only(['index', 'show']);
    }

    public function index(FiltrarHistorialIndexacionRequest $request)
    {
        $filtros = $request->validated();

        return view('busqueda-inteligente.administracion.historial.index', [
            'historial' => $this->motorIndexacionService->historial($filtros),
            'filtrosHistorial' => $filtros,
        ]);
    }

    public function show(EjecucionIndexacionMotor $ejecucion)
    {
        $ejecucion->load('usuario:id,name');

        return view('busqueda-inteligente.administracion.historial.show', compact('ejecucion'));
    }
}
