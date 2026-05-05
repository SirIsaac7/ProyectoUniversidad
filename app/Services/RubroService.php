<?php

namespace App\Services;

use App\Models\Rubro;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class RubroService
{
    public function getAll(): Collection
    {
        return Rubro::orderByDesc('id')->get();
    }

    public function create(array $data): Rubro
    {
        return Rubro::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $this->storeImage($data['imagen'] ?? null, $data['nombre']),
            'estado' => $data['estado'],
        ]);
    }

    public function update(Rubro $rubro, array $data): Rubro
    {
        $imagen = $rubro->imagen;

        if (! empty($data['imagen'])) {
            $this->deleteImage($rubro->imagen);
            $imagen = $this->storeImage($data['imagen'], $data['nombre']);
        }

        $rubro->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $imagen,
        ]);

        return $rubro;
    }

    public function toggleEstado(Rubro $rubro): Rubro
    {
        $rubro->update([
            'estado' => ! $rubro->estado,
        ]);

        return $rubro;
    }

    protected function storeImage(?UploadedFile $image, string $nombre): ?string
    {
        if (! $image) {
            return null;
        }

        $directorio = public_path('uploads/rubros');

        if (! file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreLimpio = str()->slug($nombre);
        $nombreArchivo = now()->format('Ymd_His') . '_' . $nombreLimpio . '.' . $image->getClientOriginalExtension();

        $image->move($directorio, $nombreArchivo);

        return 'uploads/rubros/' . $nombreArchivo;
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
