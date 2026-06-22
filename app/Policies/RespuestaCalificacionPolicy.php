<?php

namespace App\Policies;

use App\Models\Calificacion;
use App\Models\RespuestaCalificacion;
use App\Models\User;

class RespuestaCalificacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver calificaciones')
            || $user->can('ver mis calificaciones proveedor');
    }

    public function view(User $user, RespuestaCalificacion $respuestaCalificacion): bool
    {
        return $user->can('ver calificaciones')
            || (
                $user->can('ver mis calificaciones proveedor')
                && $user->perfilProveedor
                && (int) $respuestaCalificacion->calificacion?->cita?->solicitud?->perfil_proveedor_id === (int) $user->perfilProveedor->id
            );
    }

    public function create(User $user, ?Calificacion $calificacion = null): bool
    {
        if (! $user->can('responder mis calificaciones proveedor') || ! $user->perfilProveedor) {
            return false;
        }

        if (! $calificacion) {
            return true;
        }

        return (int) $calificacion->cita?->solicitud?->perfil_proveedor_id === (int) $user->perfilProveedor->id
            && ! $calificacion->respuesta;
    }

    public function update(User $user, RespuestaCalificacion $respuestaCalificacion): bool
    {
        return $user->can('responder mis calificaciones proveedor')
            && $user->perfilProveedor
            && ! $respuestaCalificacion->fueEditada()
            && (int) $respuestaCalificacion->calificacion?->cita?->solicitud?->perfil_proveedor_id === (int) $user->perfilProveedor->id;
    }

    public function delete(User $user, RespuestaCalificacion $respuestaCalificacion): bool
    {
        return false;
    }

    public function restore(User $user, RespuestaCalificacion $respuestaCalificacion): bool
    {
        return false;
    }

    public function forceDelete(User $user, RespuestaCalificacion $respuestaCalificacion): bool
    {
        return false;
    }
}
