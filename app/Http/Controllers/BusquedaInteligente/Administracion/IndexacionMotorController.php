<?php

namespace App\Http\Controllers\BusquedaInteligente\Administracion;

use App\Exceptions\MotorBusquedaException;
use App\Http\Controllers\BusquedaInteligente\Administracion\Concerns\RespondeErroresMotor;
use App\Http\Controllers\Controller;
use App\Services\BusquedaInteligente\Administracion\MotorIndexacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexacionMotorController extends Controller
{
    use RespondeErroresMotor;

    public function __construct(
        protected MotorIndexacionService $motorIndexacionService,
    ) {
        $this->middleware('permission:ver panel motor busqueda')->only('index');
        $this->middleware('permission:ejecutar indexacion motor busqueda')->only('store');
        $this->middleware('permission:consultar estado motor busqueda')->only('show');
    }

    public function index()
    {
        return view('busqueda-inteligente.administracion.indexacion.index', [
            'identificadorActivo' => $this->motorIndexacionService->ejecucionActiva()?->fastapi_identificador,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $ejecucion = $this->motorIndexacionService->iniciarManual($request->user());

            return response()->json([
                'ok' => true,
                'data' => [
                    'ejecucion_id' => $ejecucion->id,
                    'identificador' => $ejecucion->fastapi_identificador,
                    'mensaje' => $ejecucion->respuesta_inicio['mensaje'] ?? 'La indexacion fue aceptada por el motor.',
                ],
            ], 202);
        } catch (MotorBusquedaException $exception) {
            return $this->errorMotor(
                $exception,
                $this->motorIndexacionService->ejecucionActiva()?->fastapi_identificador,
            );
        }
    }

    public function show(string $identificador): JsonResponse
    {
        try {
            $ejecucion = $this->motorIndexacionService->actualizarPorIdentificador($identificador);

            return response()->json([
                'ok' => true,
                'data' => $this->motorIndexacionService->paraPanel($ejecucion),
            ]);
        } catch (MotorBusquedaException $exception) {
            return $this->errorMotor($exception);
        }
    }
}
