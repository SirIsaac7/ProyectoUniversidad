<?php

namespace App\Http\Controllers\BusquedaInteligente\Administracion;

use App\Exceptions\MotorBusquedaException;
use App\Http\Controllers\BusquedaInteligente\Administracion\Concerns\RespondeErroresMotor;
use App\Http\Controllers\Controller;
use App\Services\BusquedaInteligente\Administracion\EstadoMotorService;
use Illuminate\Http\JsonResponse;

class ResumenMotorController extends Controller
{
    use RespondeErroresMotor;

    public function __construct(
        protected EstadoMotorService $estadoMotorService,
    ) {
        $this->middleware('permission:ver panel motor busqueda')->only('index');
        $this->middleware('permission:consultar estado motor busqueda')->only('estado');
    }

    public function index()
    {
        return view('busqueda-inteligente.administracion.resumen.index');
    }

    public function estado(): JsonResponse
    {
        try {
            return response()->json([
                'ok' => true,
                'data' => $this->estadoMotorService->estadoServicios(),
            ]);
        } catch (MotorBusquedaException $exception) {
            return $this->errorMotor($exception);
        }
    }
}
