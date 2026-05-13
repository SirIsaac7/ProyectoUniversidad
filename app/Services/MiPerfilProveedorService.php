<?php

namespace App\Services;

use App\Models\PerfilProveedor;
use Illuminate\Http\UploadedFile;

class MiPerfilProveedorService
{
    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with([
            'user',
            'proveedorEspecialidades.especialidad.rubroTipoServicio.rubro',
            'proveedorEspecialidades.especialidad.rubroTipoServicio.tipoServicio',
            'horarios',
            'ubicacion',
            'portafolio.imagenes',
            'documentos.tipoDocumentoProveedor',
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function updatePerfilActual(array $data): PerfilProveedor
    {
        $perfilProveedor = $this->getPerfilActual();
        $fotoPortada = $perfilProveedor->foto_portada;

        if (! empty($data['foto_portada'])) {
            $this->deleteImage($perfilProveedor->foto_portada);
            $fotoPortada = $this->storeImage($data['foto_portada'], $data['nombre_publico']);
        }

        $perfilProveedor->update([
            'nombre_publico' => $data['nombre_publico'],
            'descripcion' => $data['descripcion'] ?? null,
            'foto_portada' => $fotoPortada,
            'anios_experiencia' => $data['anios_experiencia'] ?? null,
        ]);

        return $perfilProveedor->load('user');
    }

    protected function storeImage(UploadedFile $image, string $nombre): string
    {
        $directorio = public_path('uploads/perfiles-proveedores');

        if (! file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreArchivo = now()->format('Ymd_His_u') . '_' . str()->slug($nombre) . '.' . $image->getClientOriginalExtension();

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
