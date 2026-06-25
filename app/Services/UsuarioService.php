<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
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
        $avatar = $this->guardarAvatar($data['avatar'] ?? null);

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'celular' => $data['celular'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'recibe_notificaciones_whatsapp' => (bool) ($data['recibe_notificaciones_whatsapp'] ?? false),
            'avatar' => $avatar,
            'password' => $data['password'],
            'estado' => $data['estado'],
        ]);
    }

    public function updateUsuario(User $usuario, array $data): User
    {
        $celularCambio = ($data['celular'] ?? null) !== $usuario->celular;
        $avatar = $this->guardarAvatar($data['avatar'] ?? null, $usuario->avatar);

        $usuario->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'celular' => $data['celular'] ?? null,
            'celular_verificado_at' => $celularCambio ? null : $usuario->celular_verificado_at,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'recibe_notificaciones_whatsapp' => (bool) ($data['recibe_notificaciones_whatsapp'] ?? false),
            'avatar' => $avatar ?? $usuario->avatar,
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

    protected function guardarAvatar(?UploadedFile $avatar, ?string $avatarAnterior = null): ?string
    {
        if (! $avatar) {
            return null;
        }

        $carpeta = public_path('uploads/usuarios');

        if (! File::exists($carpeta)) {
            File::makeDirectory($carpeta, 0755, true);
        }

        if ($avatarAnterior && ! str_starts_with($avatarAnterior, 'http')) {
            File::delete(public_path($avatarAnterior));
        }

        $nombreArchivo = uniqid('usuario_', true) . '.' . $avatar->getClientOriginalExtension();
        $avatar->move($carpeta, $nombreArchivo);

        return 'uploads/usuarios/' . $nombreArchivo;
    }

}
