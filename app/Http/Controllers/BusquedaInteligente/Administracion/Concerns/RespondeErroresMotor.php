<?php

namespace App\Http\Controllers\BusquedaInteligente\Administracion\Concerns;

use App\Exceptions\MotorBusquedaException;
use Illuminate\Http\JsonResponse;

trait RespondeErroresMotor
{
    protected function errorMotor(MotorBusquedaException $exception, ?string $identificadorActivo = null): JsonResponse
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

        if ($exception->tipo === 'conflicto' && $identificadorActivo !== null) {
            $respuesta['identificador_activo'] = $identificadorActivo;
        }

        return response()->json($respuesta, $status);
    }
}
