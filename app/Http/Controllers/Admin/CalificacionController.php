<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCalificacionRequest;
use App\Models\Calificacion;
use App\Services\CalificacionService;

class CalificacionController extends Controller
{
    public function __construct(
        protected CalificacionService $calificacionService
    ) {
        $this->middleware('permission:ver calificaciones')->only('index');
        $this->middleware('permission:ocultar calificaciones')->only('update');
        $this->middleware('permission:eliminar calificaciones')->only('destroy');
    }

    public function index()
    {
        return view('admin.calificaciones.index');
    }

    public function update(UpdateCalificacionRequest $request, Calificacion $calificacion)
    {
        $this->authorize('update', $calificacion);

        $this->calificacionService->actualizarEstado($calificacion, $request->validated('estado'));

        return redirect()
            ->route('calificaciones.index')
            ->with('success', 'Calificación actualizada correctamente.');
    }

    public function destroy(Calificacion $calificacion)
    {
        $this->authorize('delete', $calificacion);

        $this->calificacionService->eliminar($calificacion);

        return redirect()
            ->route('calificaciones.index')
            ->with('success', 'Calificación eliminada correctamente.');
    }
}
