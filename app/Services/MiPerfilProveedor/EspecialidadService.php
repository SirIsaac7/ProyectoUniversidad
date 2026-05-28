<?php

namespace App\Services\MiPerfilProveedor;

use App\Models\Especialidad;
use App\Models\PerfilProveedor;
use App\Models\ProveedorEspecialidad;
use App\Services\ProveedorEspecialidadService;
use Illuminate\Database\Eloquent\Collection;

class EspecialidadService
{
    public function __construct(
        protected ProveedorEspecialidadService $proveedorEspecialidadService
    ) {
    }

    public function obtenerDatosVista(): array
    {
        $perfilProveedor = $this->getPerfilActual();
        $especialidadesDisponibles = $this->getEspecialidadesDisponibles();
        $especialidadesAsignadas = $perfilProveedor->proveedorEspecialidades
            ->sortByDesc('estado')
            ->sortByDesc('es_principal')
            ->values();
        $especialidadesActivas = $especialidadesAsignadas->where('estado', true);
        $especialidadPrincipal = $especialidadesActivas->firstWhere('es_principal', true);

        return [
            'perfilProveedor' => $perfilProveedor,
            'especialidadesDisponibles' => $especialidadesDisponibles,
            'especialidadesAsignadas' => $especialidadesAsignadas,
            'especialidadesActivas' => $especialidadesActivas,
            'especialidadPrincipal' => $especialidadPrincipal,
        ];
    }

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with([
            'proveedorEspecialidades' => fn ($query) => $query
                ->orderByDesc('estado')
                ->orderByDesc('es_principal')
                ->orderByDesc('id'),
            'proveedorEspecialidades.especialidad.rubroTipoServicio.rubro',
            'proveedorEspecialidades.especialidad.rubroTipoServicio.tipoServicio',
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

    public function asignarActual(array $data): ProveedorEspecialidad
    {
        return $this->proveedorEspecialidadService->createForPerfil(
            $this->getPerfilActual()->id,
            $data
        );
    }

    public function actualizarActual(ProveedorEspecialidad $proveedorEspecialidad, array $data): ProveedorEspecialidad
    {
        return $this->proveedorEspecialidadService->updateForPerfil(
            $proveedorEspecialidad,
            $this->getPerfilActual()->id,
            $data
        );
    }

    public function eliminarActual(ProveedorEspecialidad $proveedorEspecialidad): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $proveedorEspecialidad->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->proveedorEspecialidadService->bajaLogica($proveedorEspecialidad);
    }

    public function activarActual(ProveedorEspecialidad $proveedorEspecialidad): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $proveedorEspecialidad->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->proveedorEspecialidadService->activar($proveedorEspecialidad);
    }
}
