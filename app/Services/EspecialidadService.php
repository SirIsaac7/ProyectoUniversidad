<?php

namespace App\Services;

use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class EspecialidadService
{
    public function getAll(): Collection
    {
        return Especialidad::with('rubroTipoServicio.rubro', 'rubroTipoServicio.tipoServicio')
            ->orderByDesc('id')
            ->get();
    }

    public function create(array $data): Especialidad
    {
        return Especialidad::create([
            'rubro_tipo_servicio_id' => $data['rubro_tipo_servicio_id'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $this->storeImage($data['imagen'] ?? null, $data['nombre']),
            'estado' => $data['estado'],
        ]);
    }

    public function update(Especialidad $especialidad, array $data): Especialidad
    {
        $imagen = $especialidad->imagen;

        if (! empty($data['imagen'])) {
            $this->deleteImage($especialidad->imagen);
            $imagen = $this->storeImage($data['imagen'], $data['nombre']);
        }

        $especialidad->update([
            'rubro_tipo_servicio_id' => $data['rubro_tipo_servicio_id'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $imagen,
        ]);

        return $especialidad;
    }

    public function toggleEstado(Especialidad $especialidad): Especialidad
    {
        $especialidad->update([
            'estado' => ! $especialidad->estado,
        ]);

        return $especialidad;
    }

    protected function storeImage(?UploadedFile $image, string $nombre): ?string
    {
        if (! $image) {
            return null;
        }

        $directorio = public_path('uploads/especialidades');

        if (! file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreLimpio = str()->slug($nombre);
        $nombreArchivo = now()->format('Ymd_His') . '_' . $nombreLimpio . '.' . $image->getClientOriginalExtension();

        $image->move($directorio, $nombreArchivo);

        return 'uploads/especialidades/' . $nombreArchivo;
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
