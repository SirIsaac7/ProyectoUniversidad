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
            'celular' => $data['celular'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'recibe_notificaciones_whatsapp' => (bool) ($data['recibe_notificaciones_whatsapp'] ?? false),
            'password' => $data['password'],
            'estado' => $data['estado'],
        ]);
    }

    public function updateUsuario(User $usuario, array $data): User
    {
        $celularCambio = ($data['celular'] ?? null) !== $usuario->celular;

        $usuario->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'celular' => $data['celular'] ?? null,
            'celular_verificado_at' => $celularCambio ? null : $usuario->celular_verificado_at,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'recibe_notificaciones_whatsapp' => (bool) ($data['recibe_notificaciones_whatsapp'] ?? false),
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
