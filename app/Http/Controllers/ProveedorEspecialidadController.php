<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorEspecialidadRequest;
use App\Http\Requests\UpdateProveedorEspecialidadRequest;
use App\Models\Especialidad;
use App\Models\PerfilProveedor;
use App\Models\ProveedorEspecialidad;
use App\Services\ProveedorEspecialidadService;

class ProveedorEspecialidadController extends Controller
{
    public function __construct(
        protected ProveedorEspecialidadService $proveedorEspecialidadService
    ) {
        $this->middleware('permission:ver especialidades proveedor')->only('index');
        $this->middleware('permission:crear especialidades proveedor')->only(['create', 'store']);
        $this->middleware('permission:editar especialidades proveedor')->only(['edit', 'update']);
        $this->middleware('permission:eliminar especialidades proveedor')->only('destroy');
    }

    public function index()
    {
        return view('proveedor-especialidades.index');
    }

    public function create()
    {
        return view('proveedor-especialidades.create', $this->formData());
    }

    public function store(StoreProveedorEspecialidadRequest $request)
    {
        $this->proveedorEspecialidadService->create($request->validated());

        return redirect()
            ->route('proveedor-especialidades.index')
            ->with('success', 'Especialidad asignada correctamente.');
    }

    public function edit($proveedor_especialidade)
    {
        $proveedorEspecialidad = ProveedorEspecialidad::with([
            'perfilProveedor.user',
            'especialidad.rubroTipoServicio.rubro',
            'especialidad.rubroTipoServicio.tipoServicio',
        ])->findOrFail($proveedor_especialidade);

        return view('proveedor-especialidades.edit', [
            ...$this->formData($proveedorEspecialidad),
            'proveedorEspecialidad' => $proveedorEspecialidad,
        ]);
    }

    public function update(UpdateProveedorEspecialidadRequest $request, $proveedor_especialidade)
    {
        $proveedorEspecialidad = ProveedorEspecialidad::findOrFail($proveedor_especialidade);

        $this->proveedorEspecialidadService->update($proveedorEspecialidad, $request->validated());

        return redirect()
            ->route('proveedor-especialidades.index')
            ->with('success', 'Especialidad del proveedor actualizada correctamente.');
    }

    public function destroy($proveedor_especialidade)
    {
        $proveedorEspecialidad = ProveedorEspecialidad::findOrFail($proveedor_especialidade);

        $this->proveedorEspecialidadService->toggleEstado($proveedorEspecialidad);

        $mensaje = $proveedorEspecialidad->estado
            ? 'Especialidad del proveedor activada correctamente.'
            : 'Especialidad del proveedor inactivada correctamente.';

        return redirect()
            ->route('proveedor-especialidades.index')
            ->with('success', $mensaje);
    }

    protected function formData(?ProveedorEspecialidad $proveedorEspecialidad = null): array
    {
        $perfilesProveedores = PerfilProveedor::with('user')
            ->where('estado', true)
            ->orderBy('nombre_publico')
            ->get();

        $especialidades = Especialidad::with('rubroTipoServicio.rubro', 'rubroTipoServicio.tipoServicio')
            ->where('estado', true)
            ->whereHas('rubroTipoServicio', fn ($query) => $query->where('estado', true))
            ->whereHas('rubroTipoServicio.rubro', fn ($query) => $query->where('estado', true))
            ->whereHas('rubroTipoServicio.tipoServicio', fn ($query) => $query->where('estado', true))
            ->orderBy('nombre')
            ->get();

        return compact('perfilesProveedores', 'especialidades');
    }
}
