<?php

namespace App\Services\MiPerfilProveedor;

use App\Models\DocumentoProveedor;
use App\Models\Especialidad;
use App\Models\HorarioProveedor;
use App\Models\PerfilProveedor;
use App\Models\PortafolioProveedor;
use App\Models\ProveedorEspecialidad;
use App\Models\TipoDocumentoProveedor;
use App\Models\UbicacionProveedor;
use App\Services\DocumentoProveedorService;
use App\Services\HorarioProveedorService;
use App\Services\PerfilProveedorService;
use App\Services\PortafolioProveedorService;
use App\Services\ProveedorEspecialidadService;
use App\Services\UbicacionProveedorService;
use Illuminate\Database\Eloquent\Collection;

class MiPerfilProveedorService
{
    public function __construct(
        protected PerfilProveedorService $perfilProveedorService,
        protected ProveedorEspecialidadService $proveedorEspecialidadService,
        protected HorarioProveedorService $horarioProveedorService,
        protected UbicacionProveedorService $ubicacionProveedorService,
        protected PortafolioProveedorService $portafolioProveedorService,
        protected DocumentoProveedorService $documentoProveedorService,
    ) {
    }

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with([
            'user',
            'proveedorEspecialidades' => fn ($query) => $query->where('estado', true),
            'proveedorEspecialidades.especialidad.rubroTipoServicio.rubro',
            'proveedorEspecialidades.especialidad.rubroTipoServicio.tipoServicio',
            'horarios' => fn ($query) => $query->where('estado', true),
            'ubicacion',
            'portafolio' => fn ($query) => $query->where('estado', true),
            'portafolio.imagenes',
            'documentos' => fn ($query) => $query->where('estado', true),
            'documentos.tipoDocumentoProveedor',
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function getEspecialidadesDisponibles(): Collection
    {
        return Especialidad::with('rubroTipoServicio.rubro', 'rubroTipoServicio.tipoServicio')
            ->where('estado', true)
            ->whereHas('rubroTipoServicio', fn ($query) => $query->where('estado', true))
            ->whereHas('rubroTipoServicio.rubro', fn ($query) => $query->where('estado', true))
            ->whereHas('rubroTipoServicio.tipoServicio', fn ($query) => $query->where('estado', true))
            ->orderBy('nombre')
            ->get();
    }

    public function getTiposDocumentoDisponibles(): Collection
    {
        return TipoDocumentoProveedor::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();
    }

    public function updatePerfilActual(array $data): PerfilProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->perfilProveedorService->updateDatosBasicos($perfilProveedor, $data);
    }

    public function asignarEspecialidadActual(array $data): ProveedorEspecialidad
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->proveedorEspecialidadService->createForPerfil($perfilProveedor->id, $data);
    }

    public function actualizarEspecialidadActual(ProveedorEspecialidad $proveedorEspecialidad, array $data): ProveedorEspecialidad
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->proveedorEspecialidadService->updateForPerfil($proveedorEspecialidad, $perfilProveedor->id, $data);
    }

    public function crearHorarioActual(array $data): HorarioProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->horarioProveedorService->createForPerfil($perfilProveedor->id, $data);
    }

    public function actualizarHorarioActual(HorarioProveedor $horarioProveedor, array $data): HorarioProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->horarioProveedorService->updateForPerfil($horarioProveedor, $perfilProveedor->id, $data);
    }

    public function guardarUbicacionActual(array $data): UbicacionProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->ubicacionProveedorService->createOrUpdateForPerfil($perfilProveedor->id, $data);
    }

    public function crearPortafolioActual(array $data): PortafolioProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->portafolioProveedorService->createForPerfil($perfilProveedor->id, $data);
    }

    public function actualizarPortafolioActual(PortafolioProveedor $portafolioProveedor, array $data): PortafolioProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->portafolioProveedorService->updateForPerfil($portafolioProveedor, $perfilProveedor->id, $data);
    }

    public function subirDocumentoActual(array $data): DocumentoProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->documentoProveedorService->createForPerfil($perfilProveedor->id, $data);
    }

    public function actualizarDocumentoActual(DocumentoProveedor $documentoProveedor, array $data): DocumentoProveedor
    {
        $perfilProveedor = $this->getPerfilActual();

        return $this->documentoProveedorService->updateForPerfil($documentoProveedor, $perfilProveedor->id, $data);
    }

    public function eliminarEspecialidadActual(ProveedorEspecialidad $proveedorEspecialidad): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $proveedorEspecialidad->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->proveedorEspecialidadService->bajaLogica($proveedorEspecialidad);
    }

    public function eliminarHorarioActual(HorarioProveedor $horarioProveedor): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $horarioProveedor->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->horarioProveedorService->bajaLogica($horarioProveedor);
    }

    public function eliminarPortafolioActual(PortafolioProveedor $portafolioProveedor): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $portafolioProveedor->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->portafolioProveedorService->bajaLogica($portafolioProveedor);
    }

    public function eliminarDocumentoActual(DocumentoProveedor $documentoProveedor): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $documentoProveedor->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->documentoProveedorService->bajaLogica($documentoProveedor);
    }
}
