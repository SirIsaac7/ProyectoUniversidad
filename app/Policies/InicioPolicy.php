<?php

namespace App\Policies;

use App\Models\User;

class InicioPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, string $tipoInicio): bool
    {
        return match ($tipoInicio) {
            'proveedor' => $user->hasRole('PROVEEDOR')
                && $user->can('visualizar perfil proveedor'),
            'admin' => $user->hasRole('ADMINISTRADOR')
                || $user->can('ver usuarios')
                || $user->can('ver roles')
                || $user->can('ver solicitudes')
                || $user->can('ver perfiles proveedores'),
            'cliente' => $user->hasRole('CLIENTE'),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, string $tipoInicio): bool
    {
        return false;
    }

    public function delete(User $user, string $tipoInicio): bool
    {
        return false;
    }

    public function restore(User $user, string $tipoInicio): bool
    {
        return false;
    }

    public function forceDelete(User $user, string $tipoInicio): bool
    {
        return false;
    }
}
