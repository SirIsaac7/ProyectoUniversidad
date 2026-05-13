<?php

namespace App\Services;

use App\Models\HorarioProveedor;
use Illuminate\Database\Eloquent\Collection;

class HorarioProveedorService
{
    public function getAll(): Collection
    {
        return HorarioProveedor::with('perfilProveedor.user')
            ->orderBy('perfil_proveedor_id')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();
    }

    public function create(array $data): HorarioProveedor
    {
        return HorarioProveedor::create([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'dia_semana' => $data['dia_semana'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'tipo_atencion' => $data['tipo_atencion'],
            'disponible' => $data['disponible'],
        ]);
    }

    public function createForPerfil(int $perfilProveedorId, array $data): HorarioProveedor
    {
        return $this->create([
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
        ]);
    }

    public function update(HorarioProveedor $horarioProveedor, array $data): HorarioProveedor
    {
        $horarioProveedor->update([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'dia_semana' => $data['dia_semana'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'tipo_atencion' => $data['tipo_atencion'],
            'disponible' => $data['disponible'] ?? $horarioProveedor->disponible,
        ]);

        return $horarioProveedor->load('perfilProveedor.user');
    }

    public function updateForPerfil(HorarioProveedor $horarioProveedor, int $perfilProveedorId, array $data): HorarioProveedor
    {
        abort_unless((int) $horarioProveedor->perfil_proveedor_id === $perfilProveedorId, 403);

        return $this->update($horarioProveedor, [
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
        ]);
    }

    public function toggleDisponible(HorarioProveedor $horarioProveedor): HorarioProveedor
    {
        $horarioProveedor->update([
            'disponible' => ! $horarioProveedor->disponible,
        ]);

        return $horarioProveedor;
    }
}
