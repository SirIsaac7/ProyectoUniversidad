<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreEspecialidadRequest;
use App\Http\Requests\UpdateEspecialidadRequest;
use App\Models\Especialidad;
use App\Models\RubroTipoServicio;
use App\Services\EspecialidadService;

class EspecialidadController extends Controller
{
    public function __construct(
        protected EspecialidadService $especialidadService
    ) {
        $this->middleware('permission:ver especialidades')->only('index');
        $this->middleware('permission:crear especialidades')->only(['create', 'store']);
        $this->middleware('permission:editar especialidades')->only(['edit', 'update']);
        $this->middleware('permission:eliminar especialidades')->only('destroy');
    }

    public function index()
    {
        return view('admin.especialidades.index');
    }

    public function create()
    {
        $rubrosTiposServicio = RubroTipoServicio::with(['rubro', 'tipoServicio'])
            ->where('estado', true)
            ->whereHas('rubro', fn ($query) => $query->where('estado', true))
            ->whereHas('tipoServicio', fn ($query) => $query->where('estado', true))
            ->orderByDesc('id')
            ->get();

        return view('admin.especialidades.create', compact('rubrosTiposServicio'));
    }

    public function store(StoreEspecialidadRequest $request)
    {
        $this->especialidadService->create($request->validated());

        return redirect()
            ->route('especialidades.index')
            ->with('success', 'Especialidad creada correctamente.');
    }

    public function edit(Especialidad $especialidade)
    {
        $rubrosTiposServicio = RubroTipoServicio::with(['rubro', 'tipoServicio'])
            ->where('estado', true)
            ->whereHas('rubro', fn ($query) => $query->where('estado', true))
            ->whereHas('tipoServicio', fn ($query) => $query->where('estado', true))
            ->orderByDesc('id')
            ->get();

        return view('admin.especialidades.edit', [
            'especialidad' => $especialidade->load('rubroTipoServicio.rubro', 'rubroTipoServicio.tipoServicio'),
            'rubrosTiposServicio' => $rubrosTiposServicio,
        ]);
    }

    public function update(UpdateEspecialidadRequest $request, Especialidad $especialidade)
    {
        $this->especialidadService->update($especialidade, $request->validated());

        return redirect()
            ->route('especialidades.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Especialidad $especialidade)
    {
        $this->especialidadService->toggleEstado($especialidade);

        $mensaje = $especialidade->estado
            ? 'Especialidad activada correctamente.'
            : 'Especialidad inactivada correctamente.';

        return redirect()
            ->route('especialidades.index')
            ->with('success', $mensaje);
    }
}
