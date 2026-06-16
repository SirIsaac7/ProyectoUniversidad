<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Services\Cliente\BusquedaServicioService;
use Illuminate\Http\Request;

class BusquedaServicioController extends Controller
{
    public function __construct(
        protected BusquedaServicioService $busquedaServicioService
    ) {
        $this->middleware('permission:buscar servicios');
    }

    public function index(Request $request)
    {
        return view('cliente.busqueda-servicios.index', [
            'filtros' => $this->busquedaServicioService->normalizarFiltros($request->only([
                'q',
                'rubro_id',
                'tipo_servicio_id',
                'zona',
                'usar_ubicacion_actual',
                'latitud',
                'longitud',
            ])),
        ]);
    }
}
