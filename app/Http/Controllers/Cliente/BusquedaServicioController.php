<?php

namespace App\Http\Controllers\Cliente;

use App\Exceptions\MotorBusquedaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\BusquedaInteligenteRequest;
use App\Http\Requests\Cliente\BusquedaSemanticaRequest;
use App\Http\Requests\Cliente\TranscribirAudioRequest;
use App\Models\EjecucionBusquedaMotor;
use App\Services\Admin\ConfiguracionBusquedaVisualService;
use App\Services\Cliente\BusquedaServicioService;
use App\Services\Cliente\MotorBusquedaSeguimientoService;
use Illuminate\Http\Request;

class BusquedaServicioController extends Controller
{
    public function __construct(
        protected BusquedaServicioService $busquedaServicioService,
        protected MotorBusquedaSeguimientoService $motorBusquedaSeguimientoService,
        protected ConfiguracionBusquedaVisualService $configuracionBusquedaVisualService,
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
            'personalizacionBusqueda' => $this->configuracionBusquedaVisualService->paraCliente(),
        ]);
    }

    public function semantica(BusquedaSemanticaRequest $request)
    {
        try {
            $data = $request->validated();
            $ejecucion = $this->motorBusquedaSeguimientoService->iniciar($request->user(), $data['consulta'], [
                'disponible' => $data['disponible'] ?? null,
                'tipo_atencion' => $data['tipo_atencion'] ?? null,
            ]);

            return response()->json([
                'ok' => true,
                'data' => $this->motorBusquedaSeguimientoService->paraCliente($ejecucion),
            ], 202);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->errorBusquedaSemantica($exception);
        }
    }

    public function estadoSemantica(EjecucionBusquedaMotor $ejecucion)
    {
        $this->authorize('view', $ejecucion);

        try {
            $ejecucion = $this->motorBusquedaSeguimientoService->actualizar($ejecucion);

            return response()->json([
                'ok' => true,
                'data' => $this->motorBusquedaSeguimientoService->paraCliente($ejecucion),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->errorBusquedaSemantica($exception);
        }
    }

    public function resultadosSemantica(EjecucionBusquedaMotor $ejecucion)
    {
        $this->authorize('view', $ejecucion);

        try {
            $resultado = $this->motorBusquedaSeguimientoService->resultados($ejecucion);

            return response()->json([
                'ok' => true,
                'consulta' => $resultado['consulta'],
                'total' => $resultado['total'],
                'total_motor' => $resultado['total_motor'],
                'html' => view('cliente.busqueda-servicios.partials.resultado-semantico', [
                    'proveedores' => $resultado['proveedores'],
                    'tiposAtencion' => $this->busquedaServicioService->tiposAtencion(),
                    'personalizacionBusqueda' => $this->configuracionBusquedaVisualService->paraCliente(),
                ])->render(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->errorBusquedaSemantica($exception);
        }
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

    public function transcribir(TranscribirAudioRequest $request)
    {
        try {
            return response()->json([
                'ok' => true,
                'texto' => $this->busquedaServicioService->transcribirAudio($request->validated()),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'mensaje' => $exception->getMessage() ?: 'No se pudo transcribir el audio.',
            ], 422);
        }
    }

    private function errorBusquedaSemantica(\Throwable $exception)
    {
        $status = $exception instanceof MotorBusquedaException
            ? match ($exception->tipo) {
                'conflicto' => 409,
                'identificador_invalido' => 422,
                'respuesta_http' => in_array($exception->statusHttp, [404, 409, 422], true)
                    ? $exception->statusHttp
                    : 503,
                default => 503,
            }
        : 503;

        return response()->json([
            'ok' => false,
            'mensaje' => $exception instanceof MotorBusquedaException
                ? $exception->getMessage()
                : 'No pudimos completar la búsqueda. Intenta nuevamente.',
        ], $status);
    }
}
