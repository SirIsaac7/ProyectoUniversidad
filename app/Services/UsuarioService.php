<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class UsuarioService
{
    public function getAllUsuarios()
    {
        return User::with('roles')->orderBy('id', 'desc')->get();
    }

    public function createUsuario(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'estado' => $data['estado'],
        ]);
    }

    public function updateUsuario(User $usuario, array $data): User
    {
        $usuario->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return $usuario->load('roles');
    }

    public function toggleEstado(User $usuario): User
    {
        $usuario->update([
            'estado' => ! $usuario->estado,
        ]);

        return $usuario;
    }
    public function getAllRoles(): Collection
    {
        return Role::orderBy('name')->get();
    }

    public function syncUserRole(User $usuario, ?string $roleName): User
    {
        if (blank($roleName)) {
            $usuario->syncRoles([]);
        } else {
            $usuario->syncRoles([$roleName]);
        }

        return $usuario->load('roles');
    }

}
