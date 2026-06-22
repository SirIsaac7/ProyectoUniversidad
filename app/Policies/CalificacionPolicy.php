<?php

namespace App\Policies;

use App\Models\Calificacion;
use App\Models\User;

class CalificacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver calificaciones')
            || $user->can('ver mis calificaciones')
            || $user->can('ver mis calificaciones proveedor');
    }

    public function view(User $user, Calificacion $calificacion): bool
    {
        return $user->can('ver calificaciones')
            || (
                $user->can('ver mis calificaciones')
                && (int) $calificacion->cita?->solicitud?->cliente_user_id === (int) $user->id
            )
            || (
                $user->can('ver mis calificaciones proveedor')
                && $user->perfilProveedor
                && (int) $calificacion->cita?->solicitud?->perfil_proveedor_id === (int) $user->perfilProveedor->id
            );
    }

    public function create(User $user): bool
    {
        return $user->can('crear mis calificaciones');
    }

    public function update(User $user, Calificacion $calificacion): bool
    {
        return $user->can('ocultar calificaciones');
    }

    public function delete(User $user, Calificacion $calificacion): bool
    {
        return $user->can('eliminar calificaciones');
    }

    public function restore(User $user, Calificacion $calificacion): bool
    {
        return false;
    }

    public function forceDelete(User $user, Calificacion $calificacion): bool
    {
        return false;
    }
}
