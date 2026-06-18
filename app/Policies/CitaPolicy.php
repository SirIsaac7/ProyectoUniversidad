<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

class CitaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver citas')
            || $user->can('ver mis citas');
    }

    public function view(User $user, Cita $cita): bool
    {
        return $user->can('ver citas')
            || (int) $cita->solicitud?->cliente_user_id === (int) $user->id
            || (
                $user->perfilProveedor
                && (int) $cita->solicitud?->perfil_proveedor_id === (int) $user->perfilProveedor->id
            );
    }

    public function create(User $user): bool
    {
        return $user->can('crear mis citas proveedor');
    }

    public function update(User $user, Cita $cita): bool
    {
        return $user->can('editar mis citas proveedor')
            && $user->perfilProveedor
            && (int) $cita->solicitud?->perfil_proveedor_id === (int) $user->perfilProveedor->id
            && ! in_array($cita->estado, ['completada', 'cancelada', 'no_asistio', 'vencida'], true);
    }

    public function delete(User $user, Cita $cita): bool
    {
        if ($user->can('eliminar citas')) {
            return ! in_array($cita->estado, ['completada', 'cancelada', 'vencida'], true);
        }

        return $user->can('cancelar mis citas proveedor')
            && $user->perfilProveedor
            && (int) $cita->solicitud?->perfil_proveedor_id === (int) $user->perfilProveedor->id
            && ! in_array($cita->estado, ['completada', 'cancelada', 'vencida'], true);
    }

    public function restore(User $user, Cita $cita): bool
    {
        return false;
    }

    public function forceDelete(User $user, Cita $cita): bool
    {
        return false;
    }
}
