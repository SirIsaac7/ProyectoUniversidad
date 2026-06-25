<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\BusquedaInteligenteRequest;
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

    public function inteligente(BusquedaInteligenteRequest $request)
    {
        try {
            $resultado = $this->busquedaServicioService->busquedaInteligente($request->validated());

            return response()->json([
                'ok' => true,
                'mensaje' => $resultado['api']['mensaje'] ?? 'Busqueda inteligente completada.',
                'clasificacion' => [
                    'tipo_dispositivo' => $resultado['api']['tipo_dispositivo'] ?? null,
                    'marca' => $resultado['api']['marca'] ?? null,
                    'confianza' => $resultado['api']['confianza'] ?? null,
                    'modo_usado' => $resultado['api']['modo_usado'] ?? null,
                    'palabras_clave' => $resultado['api']['palabras_clave'] ?? [],
                ],
                'total' => $resultado['total'],
                'total_api' => $resultado['api']['total_encontrados'] ?? null,
                'ubicacion_cliente' => $resultado['api']['ubicacion_cliente'] ?? null,
                'html' => view('cliente.busqueda-servicios.partials.resultado-inteligente', [
                    'proveedores' => $resultado['proveedores'],
                    'clasificacion' => $resultado['api'],
                    'tiposAtencion' => $this->busquedaServicioService->tiposAtencion(),
                ])->render(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'mensaje' => $exception->getMessage() ?: 'No se pudo completar la busqueda inteligente.',
            ], 422);
        }
    }
}
