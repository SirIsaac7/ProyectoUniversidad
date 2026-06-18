<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;

class SolicitudPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver solicitudes')
            || $user->can('ver mis solicitudes')
            || $user->can('ver mis solicitudes proveedor');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Solicitud $solicitud): bool
    {
        return $user->can('ver solicitudes')
            || (int) $solicitud->cliente_user_id === (int) $user->id
            || (
                $user->perfilProveedor
                 && (int) $solicitud->perfil_proveedor_id === (int) $user->perfilProveedor->id
            );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear mis solicitudes');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Solicitud $solicitud): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Solicitud $solicitud): bool
    {
        if ($user->can('eliminar solicitudes')) {
            return ! in_array($solicitud->estado, ['cancelada', 'finalizada'], true);
        }

        return $user->can('cancelar mis solicitudes')
            && (int) $solicitud->cliente_user_id === (int) $user->id
            && ! in_array($solicitud->estado, ['cancelada', 'finalizada'], true);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Solicitud $solicitud): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Solicitud $solicitud): bool
    {
        return false;
    }

    public function gestionarProveedor(User $user, Solicitud $solicitud): bool
    {
        return $user->perfilProveedor
            && (int) $solicitud->perfil_proveedor_id === (int) $user->perfilProveedor->id
            && $solicitud->estado === 'pendiente';
    }
}
