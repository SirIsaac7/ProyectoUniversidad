<?php

namespace App\Services;

use App\Models\PerfilProveedor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class PerfilProveedorService
{
    public function getAll(): Collection
    {
        return PerfilProveedor::with('user')
            ->orderByDesc('id')
            ->get();
    }

    public function create(array $data): PerfilProveedor
    {
        return PerfilProveedor::create([
            'user_id' => $data['user_id'],
            'nombre_publico' => $data['nombre_publico'],
            'descripcion' => $data['descripcion'] ?? null,
            'foto_portada' => $this->storeImage($data['foto_portada'] ?? null, $data['nombre_publico']),
            'anios_experiencia' => $data['anios_experiencia'] ?? null,
            'estado_verificacion' => $data['estado_verificacion'],
            'motivo_rechazo' => $data['motivo_rechazo'] ?? null,
            'estado' => $data['estado'],
        ]);
    }

    public function update(PerfilProveedor $perfilProveedor, array $data): PerfilProveedor
    {
        $fotoPortada = $perfilProveedor->foto_portada;

        if (! empty($data['foto_portada'])) {
            $this->deleteImage($perfilProveedor->foto_portada);
            $fotoPortada = $this->storeImage($data['foto_portada'], $data['nombre_publico']);
        }

        $perfilProveedor->update([
            'user_id' => $data['user_id'],
            'nombre_publico' => $data['nombre_publico'],
            'descripcion' => $data['descripcion'] ?? null,
            'foto_portada' => $fotoPortada,
            'anios_experiencia' => $data['anios_experiencia'] ?? null,
            'estado_verificacion' => $data['estado_verificacion'],
            'motivo_rechazo' => $data['motivo_rechazo'] ?? null,
        ]);

        return $perfilProveedor->load('user');
    }

    public function toggleEstado(PerfilProveedor $perfilProveedor): PerfilProveedor
    {
        $perfilProveedor->update([
            'estado' => ! $perfilProveedor->estado,
        ]);

        return $perfilProveedor;
    }

    protected function storeImage(?UploadedFile $image, string $nombre): ?string
    {
        if (! $image) {
            return null;
        }

        $directorio = public_path('uploads/perfiles-proveedores');

        if (! file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreLimpio = str()->slug($nombre);
        $nombreArchivo = now()->format('Ymd_His') . '_' . $nombreLimpio . '.' . $image->getClientOriginalExtension();

        $image->move($directorio, $nombreArchivo);

        return 'uploads/perfiles-proveedores/' . $nombreArchivo;
    }

    protected function deleteImage(?string $ruta): void
    {
        if (! $ruta) {
            return;
        }

        $rutaCompleta = public_path($ruta);

        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
    }
}
