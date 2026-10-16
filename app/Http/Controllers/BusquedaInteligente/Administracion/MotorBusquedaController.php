<?php

namespace App\Http\Controllers\BusquedaInteligente\Administracion;

use App\Exceptions\MotorBusquedaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusquedaInteligente\Administracion\UpdatePersonalizacionBusquedaRequest;
use App\Http\Requests\BusquedaInteligente\Administracion\UpdateProgramacionIndexacionRequest;
use App\Models\EjecucionIndexacionMotor;
use App\Services\BusquedaInteligente\Administracion\ConfiguracionBusquedaVisualService;
use App\Services\BusquedaInteligente\Administracion\ConfiguracionIndexacionMotorService;
use App\Services\BusquedaInteligente\Administracion\MotorAdministracionService;
use App\Services\BusquedaInteligente\Administracion\MotorIndexacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MotorBusquedaController extends Controller
{
    public function __construct(
        protected MotorAdministracionService $motorAdministracionService,
        protected MotorIndexacionService $motorIndexacionService,
        protected ConfiguracionIndexacionMotorService $configuracionIndexacionService,
        protected ConfiguracionBusquedaVisualService $configuracionBusquedaVisualService,
    ) {
        $this->middleware('permission:ver panel motor busqueda')->only('index');
        $this->middleware('permission:consultar estado motor busqueda')->only(['estado', 'consultarIndexacion']);
        $this->middleware('permission:ejecutar indexacion motor busqueda')->only('iniciarIndexacion');
        $this->middleware('permission:configurar programacion indexaciones motor')->only('actualizarProgramacion');
        $this->middleware('permission:ver historial indexaciones motor')->only('detalleHistorial');
        $this->middleware('permission:modificar personalizacion busqueda')->only('actualizarPersonalizacion');
        $this->middleware('permission:restaurar personalizacion busqueda')->only('restaurarPersonalizacion');
    }

    public function index(Request $request)
    {
        $puedeVerHistorial = $request->user()->can('ver historial indexaciones motor');
        $filtros = [];
        $historial = null;

        if ($puedeVerHistorial) {
            $filtros = $request->validate([
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
                'tipo' => ['nullable', 'in:manual,automatica'],
                'estado' => ['nullable', 'in:pendiente,ejecucion,completado,error,desconocido'],
            ]);
            $historial = $this->motorIndexacionService->historial($filtros);
        }

        return view('busqueda-inteligente.administracion.index', [
            'identificadorActivo' => $this->motorAdministracionService->identificadorActivo(),
            'configuracion' => $this->configuracionIndexacionService->configuracion(),
            'proximaEjecucion' => $this->configuracionIndexacionService->proximaEjecucion(),
            'historial' => $historial,
            'filtrosHistorial' => $filtros,
            'personalizacionBusqueda' => $this->configuracionBusquedaVisualService->configuracion(),
            'personalizacionCliente' => $this->configuracionBusquedaVisualService->paraCliente(),
            'fasesBusqueda' => ConfiguracionBusquedaVisualService::FASES,
            'familiasBusqueda' => $this->configuracionBusquedaVisualService->familiasTipograficas(),
        ]);
    }

    public function estado(): JsonResponse
    {
        try {
            return response()->json([
                'ok' => true,
                'data' => $this->motorAdministracionService->estadoServicios(),
            ]);
        } catch (MotorBusquedaException $exception) {
            return $this->errorMotor($exception);
        }
    }

    public function iniciarIndexacion(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'ok' => true,
                'data' => $this->motorAdministracionService->iniciarIndexacion($request->user()),
            ], 202);
        } catch (MotorBusquedaException $exception) {
            return $this->errorMotor($exception);
        }
    }

    public function consultarIndexacion(string $identificador): JsonResponse
    {
        try {
            return response()->json([
                'ok' => true,
                'data' => $this->motorAdministracionService->consultarIndexacion($identificador),
            ]);
        } catch (MotorBusquedaException $exception) {
            return $this->errorMotor($exception);
        }
    }

    public function actualizarProgramacion(UpdateProgramacionIndexacionRequest $request)
    {
        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');
        $this->configuracionIndexacionService->actualizar($data);

        return redirect()
            ->route('motor-busqueda.index')
            ->with('success', 'Programación de indexación actualizada correctamente.');
    }

    public function detalleHistorial(EjecucionIndexacionMotor $ejecucion)
    {
        $ejecucion->load('usuario:id,name');

        return view('busqueda-inteligente.administracion.detalle', compact('ejecucion'));
    }

    public function actualizarPersonalizacion(UpdatePersonalizacionBusquedaRequest $request)
    {
        $this->configuracionBusquedaVisualService->actualizar($request->validated());

        return redirect()
            ->route('motor-busqueda.index')
            ->with('success', 'Personalización de búsqueda actualizada correctamente.');
    }

    public function restaurarPersonalizacion()
    {
        $this->configuracionBusquedaVisualService->restaurar();

        return redirect()
            ->route('motor-busqueda.index')
            ->with('success', 'Se restauraron los valores predeterminados de la búsqueda.');
    }

    private function errorMotor(MotorBusquedaException $exception): JsonResponse
    {
        $status = match ($exception->tipo) {
            'conflicto' => 409,
            'identificador_invalido' => 422,
            'respuesta_http' => in_array($exception->statusHttp, [404, 409, 422], true)
                ? $exception->statusHttp
                : 502,
            default => 503,
        };
        $respuesta = [
            'ok' => false,
            'mensaje' => $exception->getMessage(),
            'tipo' => $exception->tipo,
        ];

        if ($exception->tipo === 'conflicto') {
            $respuesta['identificador_activo'] = $this->motorAdministracionService->identificadorActivo();
        }

        return response()->json($respuesta, $status);
    }
}
