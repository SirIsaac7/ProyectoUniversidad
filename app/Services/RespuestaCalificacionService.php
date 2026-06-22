<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\RespuestaCalificacion;
use App\Models\User;

class RespuestaCalificacionService
{
    public function __construct(
        protected NotificacionService $notificacionService
    ) {
    }

    public function createDesdeProveedor(User $proveedor, Calificacion $calificacion, array $data): RespuestaCalificacion
    {
        $respuesta = RespuestaCalificacion::create([
            'calificacion_id' => $calificacion->id,
            'user_id' => $proveedor->id,
            'respuesta' => $data['respuesta'],
            'estado' => 'visible',
        ]);

        $calificacion->loadMissing('cita.solicitud.cliente', 'cita.solicitud.perfilProveedor');
        $cliente = $calificacion->cita?->solicitud?->cliente;
        $nombreProveedor = $calificacion->cita?->solicitud?->perfilProveedor?->nombre_publico
            ?? $proveedor->name
            ?? 'El proveedor';

        if ($cliente) {
            $this->notificacionService->respuestaCalificacionParaCliente(
                cliente: $cliente,
                proveedor: $nombreProveedor,
                url: route('cliente.calificaciones.index')
            );
        }

        return $respuesta;
    }

    public function update(RespuestaCalificacion $respuestaCalificacion, array $data): RespuestaCalificacion
    {
        if ($respuestaCalificacion->fueEditada()) {
            return $respuestaCalificacion;
        }

        $respuestaCalificacion->update([
            'respuesta' => $data['respuesta'],
            'estado' => $data['estado'] ?? $respuestaCalificacion->estado,
        ]);

        return $respuestaCalificacion;
    }

}
