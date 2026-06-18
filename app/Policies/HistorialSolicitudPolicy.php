<?php

namespace App\Policies;

use App\Models\HistorialSolicitud;
use App\Models\User;

class HistorialSolicitudPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver solicitudes')
            || $user->can('ver mis solicitudes')
            || $user->can('ver mis solicitudes proveedor');
    }

    public function view(User $user, HistorialSolicitud $historialSolicitud): bool
    {
        $solicitud = $historialSolicitud->solicitud;

        return $user->can('ver solicitudes')
            || (int) $solicitud?->cliente_user_id === (int) $user->id
            || (
                $user->perfilProveedor
                && (int) $solicitud?->perfil_proveedor_id === (int) $user->perfilProveedor->id
            );
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, HistorialSolicitud $historialSolicitud): bool
    {
        return false;
    }

    public function delete(User $user, HistorialSolicitud $historialSolicitud): bool
    {
        return false;
    }

    public function restore(User $user, HistorialSolicitud $historialSolicitud): bool
    {
        return false;
    }

    public function forceDelete(User $user, HistorialSolicitud $historialSolicitud): bool
    {
        return false;
    }
}
