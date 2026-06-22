<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAspectoCalificacionRequest;
use App\Http\Requests\UpdateAspectoCalificacionRequest;
use App\Models\AspectoCalificacion;
use App\Services\AspectoCalificacionService;

class AspectoCalificacionController extends Controller
{
    public function __construct(
        protected AspectoCalificacionService $aspectoCalificacionService
    ) {
        $this->middleware('permission:ver aspectos calificacion')->only('index');
        $this->middleware('permission:crear aspectos calificacion')->only(['create', 'store']);
        $this->middleware('permission:editar aspectos calificacion')->only(['edit', 'update']);
        $this->middleware('permission:eliminar aspectos calificacion')->only('destroy');
    }

    public function index()
    {
        return view('admin.aspectos-calificacion.index');
    }

    public function create()
    {
        return view('admin.aspectos-calificacion.create');
    }

    public function store(StoreAspectoCalificacionRequest $request)
    {
        $this->aspectoCalificacionService->create($request->validated());

        return redirect()
            ->route('aspectos-calificacion.index')
            ->with('success', 'Aspecto de calificación creado correctamente.');
    }

    public function edit(AspectoCalificacion $aspecto_calificacion)
    {
        return view('admin.aspectos-calificacion.edit', [
            'aspectoCalificacion' => $aspecto_calificacion,
        ]);
    }

    public function update(UpdateAspectoCalificacionRequest $request, AspectoCalificacion $aspecto_calificacion)
    {
        $this->aspectoCalificacionService->update($aspecto_calificacion, $request->validated());

        return redirect()
            ->route('aspectos-calificacion.index')
            ->with('success', 'Aspecto de calificación actualizado correctamente.');
    }

    public function destroy(AspectoCalificacion $aspecto_calificacion)
    {
        $this->aspectoCalificacionService->toggleEstado($aspecto_calificacion);

        $mensaje = $aspecto_calificacion->estado
            ? 'Aspecto de calificación activado correctamente.'
            : 'Aspecto de calificación inactivado correctamente.';

        return redirect()
            ->route('aspectos-calificacion.index')
            ->with('success', $mensaje);
    }
}
