<?php

namespace App\Services;

use App\Models\TipoServicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class TipoServicioService
{
    public function getAll(): Collection
    {
        return TipoServicio::with('rubros')->orderByDesc('id')->get();
    }

    public function create(array $data): TipoServicio
    {
        $tipoServicio = TipoServicio::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $this->storeImage($data['imagen'] ?? null, $data['nombre']),
            'estado' => $data['estado'],
        ]);

        $tipoServicio->rubros()->sync(
            collect($data['rubros'])->mapWithKeys(fn ($rubroId) => [
                $rubroId => ['estado' => true],
            ])->toArray()
        );

        return $tipoServicio->load('rubros');
    }

    public function update(TipoServicio $tipoServicio, array $data): TipoServicio
    {
        $imagen = $tipoServicio->imagen;

        if (! empty($data['imagen'])) {
            $this->deleteImage($tipoServicio->imagen);
            $imagen = $this->storeImage($data['imagen'], $data['nombre']);
        }

        $tipoServicio->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $imagen,
        ]);

        $tipoServicio->rubros()->sync(
            collect($data['rubros'])->mapWithKeys(fn ($rubroId) => [
                $rubroId => ['estado' => true],
            ])->toArray()
        );

        return $tipoServicio->load('rubros');
    }

    public function toggleEstado(TipoServicio $tipoServicio): TipoServicio
    {
        $tipoServicio->update([
            'estado' => ! $tipoServicio->estado,
        ]);

        return $tipoServicio;
    }

    protected function storeImage(?UploadedFile $image, string $nombre): ?string
    {
        if (! $image) {
            return null;
        }

        $directorio = public_path('uploads/tipos-servicio');

        if (! file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreLimpio = str()->slug($nombre);
        $nombreArchivo = now()->format('Ymd_His') . '_' . $nombreLimpio . '.' . $image->getClientOriginalExtension();

        $image->move($directorio, $nombreArchivo);

        return 'uploads/tipos-servicio/' . $nombreArchivo;
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
