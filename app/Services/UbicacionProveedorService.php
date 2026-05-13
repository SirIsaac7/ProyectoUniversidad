<?php

namespace App\Services;

use App\Models\UbicacionProveedor;
use Illuminate\Database\Eloquent\Collection;

class UbicacionProveedorService
{
    public function getAll(): Collection
    {
        return UbicacionProveedor::with('perfilProveedor.user')
            ->orderByDesc('id')
            ->get();
    }

    public function create(array $data): UbicacionProveedor
    {
        return UbicacionProveedor::create([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'zona' => $data['zona'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'latitud' => $data['latitud'],
            'longitud' => $data['longitud'],
            'radio_cobertura_km' => $data['radio_cobertura_km'] ?? null,
        ]);
    }

    public function createOrUpdateForPerfil(int $perfilProveedorId, array $data): UbicacionProveedor
    {
        $ubicacionProveedor = UbicacionProveedor::firstOrNew([
            'perfil_proveedor_id' => $perfilProveedorId,
        ]);

        $ubicacionProveedor->fill([
            'zona' => $data['zona'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'latitud' => $data['latitud'],
            'longitud' => $data['longitud'],
            'radio_cobertura_km' => $data['radio_cobertura_km'] ?? null,
        ]);

        $ubicacionProveedor->save();

        return $ubicacionProveedor->load('perfilProveedor.user');
    }

    public function update(UbicacionProveedor $ubicacionProveedor, array $data): UbicacionProveedor
    {
        $ubicacionProveedor->update([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'zona' => $data['zona'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'latitud' => $data['latitud'],
            'longitud' => $data['longitud'],
            'radio_cobertura_km' => $data['radio_cobertura_km'] ?? null,
        ]);

        return $ubicacionProveedor->load('perfilProveedor.user');
    }

    public function updateForPerfil(UbicacionProveedor $ubicacionProveedor, int $perfilProveedorId, array $data): UbicacionProveedor
    {
        abort_unless((int) $ubicacionProveedor->perfil_proveedor_id === $perfilProveedorId, 403);

        return $this->update($ubicacionProveedor, [
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
        ]);
    }

    public function delete(UbicacionProveedor $ubicacionProveedor): void
    {
        $ubicacionProveedor->delete();
    }
}
