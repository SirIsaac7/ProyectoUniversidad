<?php

namespace App\Services;

use App\Models\ProveedorEspecialidad;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProveedorEspecialidadService
{
    public function getAll(): Collection
    {
        return ProveedorEspecialidad::with([
            'perfilProveedor.user',
            'especialidad.rubroTipoServicio.rubro',
            'especialidad.rubroTipoServicio.tipoServicio',
        ])
            ->orderByDesc('id')
            ->get();
    }

    public function create(array $data): ProveedorEspecialidad
    {
        return DB::transaction(function () use ($data) {
            $proveedorEspecialidad = ProveedorEspecialidad::where('perfil_proveedor_id', $data['perfil_proveedor_id'])
                ->where('especialidad_id', $data['especialidad_id'])
                ->first();

            if ($proveedorEspecialidad) {
                $this->resetPrincipalIfNeeded(
                    (int) $data['perfil_proveedor_id'],
                    (bool) $data['es_principal'],
                    $proveedorEspecialidad->id
                );

                $proveedorEspecialidad->update([
                    'es_principal' => $data['es_principal'],
                    'estado' => $data['estado'],
                ]);

                return $proveedorEspecialidad->load([
                    'perfilProveedor.user',
                    'especialidad.rubroTipoServicio.rubro',
                    'especialidad.rubroTipoServicio.tipoServicio',
                ]);
            }

            $this->resetPrincipalIfNeeded(
                (int) $data['perfil_proveedor_id'],
                (bool) $data['es_principal']
            );

            return ProveedorEspecialidad::create([
                'perfil_proveedor_id' => $data['perfil_proveedor_id'],
                'especialidad_id' => $data['especialidad_id'],
                'es_principal' => $data['es_principal'],
                'estado' => $data['estado'],
            ]);
        });
    }

    public function createForPerfil(int $perfilProveedorId, array $data): ProveedorEspecialidad
    {
        return $this->create([
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
            'estado' => true,
        ]);
    }

    public function update(ProveedorEspecialidad $proveedorEspecialidad, array $data): ProveedorEspecialidad
    {
        return DB::transaction(function () use ($proveedorEspecialidad, $data) {
            $this->resetPrincipalIfNeeded(
                (int) $data['perfil_proveedor_id'],
                (bool) $data['es_principal'],
                $proveedorEspecialidad->id
            );

            $proveedorEspecialidad->update([
                'perfil_proveedor_id' => $data['perfil_proveedor_id'],
                'especialidad_id' => $data['especialidad_id'],
                'es_principal' => $data['es_principal'],
            ]);

            return $proveedorEspecialidad->load([
                'perfilProveedor.user',
                'especialidad.rubroTipoServicio.rubro',
                'especialidad.rubroTipoServicio.tipoServicio',
            ]);
        });
    }

    public function updateForPerfil(ProveedorEspecialidad $proveedorEspecialidad, int $perfilProveedorId, array $data): ProveedorEspecialidad
    {
        abort_unless((int) $proveedorEspecialidad->perfil_proveedor_id === $perfilProveedorId, 403);

        return $this->update($proveedorEspecialidad, [
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
        ]);
    }

    public function toggleEstado(ProveedorEspecialidad $proveedorEspecialidad): ProveedorEspecialidad
    {
        $nuevoEstado = ! $proveedorEspecialidad->estado;

        $proveedorEspecialidad->update([
            'estado' => $nuevoEstado,
            'es_principal' => $nuevoEstado ? $proveedorEspecialidad->es_principal : false,
        ]);

        return $proveedorEspecialidad;
    }

    public function bajaLogica(ProveedorEspecialidad $proveedorEspecialidad): ProveedorEspecialidad
    {
        $proveedorEspecialidad->update([
            'estado' => false,
            'es_principal' => false,
        ]);

        return $proveedorEspecialidad;
    }

    public function activar(ProveedorEspecialidad $proveedorEspecialidad): ProveedorEspecialidad
    {
        $proveedorEspecialidad->update([
            'estado' => true,
        ]);

        return $proveedorEspecialidad;
    }

    protected function resetPrincipalIfNeeded(int $perfilProveedorId, bool $esPrincipal, ?int $exceptId = null): void
    {
        if (! $esPrincipal) {
            return;
        }

        ProveedorEspecialidad::where('perfil_proveedor_id', $perfilProveedorId)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->update(['es_principal' => false]);
    }
}
