<?php

namespace App\Services;

use App\Models\PortafolioProveedor;
use App\Models\PortafolioProveedorImagen;
use Illuminate\Http\UploadedFile;

class PortafolioProveedorService
{
    public function create(array $data): PortafolioProveedor
    {
        $portafolioProveedor = PortafolioProveedor::create([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'fecha_trabajo' => $data['fecha_trabajo'] ?? null,
            'estado' => $data['estado'],
        ]);

        $this->storeImages($portafolioProveedor, $data);

        return $portafolioProveedor->load('perfilProveedor.user', 'imagenes');
    }

    public function createForPerfil(int $perfilProveedorId, array $data): PortafolioProveedor
    {
        return $this->create([
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
            'estado' => true,
        ]);
    }

    public function update(PortafolioProveedor $portafolioProveedor, array $data): PortafolioProveedor
    {
        $portafolioProveedor->update([
            'perfil_proveedor_id' => $data['perfil_proveedor_id'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'fecha_trabajo' => $data['fecha_trabajo'] ?? null,
            'estado' => $data['estado'],
        ]);

        $this->updateExistingImages($portafolioProveedor, $data);
        $this->storeImages($portafolioProveedor, $data);

        return $portafolioProveedor->load('perfilProveedor.user', 'imagenes');
    }

    public function updateForPerfil(PortafolioProveedor $portafolioProveedor, int $perfilProveedorId, array $data): PortafolioProveedor
    {
        abort_unless((int) $portafolioProveedor->perfil_proveedor_id === $perfilProveedorId, 403);

        return $this->update($portafolioProveedor, [
            ...$data,
            'perfil_proveedor_id' => $perfilProveedorId,
            'estado' => $portafolioProveedor->estado,
        ]);
    }

    public function toggleEstado(PortafolioProveedor $portafolioProveedor): PortafolioProveedor
    {
        $portafolioProveedor->update([
            'estado' => ! $portafolioProveedor->estado,
        ]);

        return $portafolioProveedor;
    }

    protected function storeImages(PortafolioProveedor $portafolioProveedor, array $data): void
    {
        $processedImages = [];

        foreach (($data['imagenes'] ?? []) as $index => $image) {
            if (! $image instanceof UploadedFile || ! $image->isValid()) {
                continue;
            }

            $imageSignature = $image->getClientOriginalName() . '|' . $image->getSize() . '|' . $image->getMimeType();

            if (in_array($imageSignature, $processedImages, true)) {
                continue;
            }

            $processedImages[] = $imageSignature;

            PortafolioProveedorImagen::create([
                'portafolio_proveedor_id' => $portafolioProveedor->id,
                'imagen' => $this->storeImage($image, $portafolioProveedor->titulo),
                'titulo' => $data['imagenes_titulo'][$index] ?? null,
                'descripcion' => $data['imagenes_descripcion'][$index] ?? null,
                'estado' => true,
            ]);
        }
    }

    protected function updateExistingImages(PortafolioProveedor $portafolioProveedor, array $data): void
    {
        foreach (($data['imagenes_existentes'] ?? []) as $imagenId => $imagenData) {
            $imagen = $portafolioProveedor->imagenes()->find($imagenId);

            if (! $imagen) {
                continue;
            }

            $imagen->update([
                'titulo' => $imagenData['titulo'] ?? null,
                'descripcion' => $imagenData['descripcion'] ?? null,
                'estado' => (bool) ($imagenData['estado'] ?? false),
            ]);
        }
    }

    protected function storeImage(UploadedFile $image, string $titulo): string
    {
        $directorio = public_path('uploads/portafolio-proveedor');

        if (! file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreLimpio = str()->slug($titulo);
        $nombreArchivo = now()->format('Ymd_His_u') . '_' . $nombreLimpio . '.' . $image->getClientOriginalExtension();

        $image->move($directorio, $nombreArchivo);

        return 'uploads/portafolio-proveedor/' . $nombreArchivo;
    }
}
