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

    public function delete(UbicacionProveedor $ubicacionProveedor): void
    {
        $ubicacionProveedor->delete();
    }
}
