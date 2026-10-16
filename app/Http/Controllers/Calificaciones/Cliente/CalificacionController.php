<?php

namespace App\Http\Controllers\Calificaciones\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calificaciones\Cliente\StoreCalificacionRequest;
use App\Models\Calificacion;
use App\Services\Calificaciones\AspectoCalificacionService;
use App\Services\Calificaciones\CalificacionService;

class CalificacionController extends Controller
{
    public function __construct(
        protected CalificacionService $calificacionService,
        protected AspectoCalificacionService $aspectoCalificacionService
    ) {
        $this->middleware('permission:ver mis calificaciones')->only('index');
        $this->middleware('permission:crear mis calificaciones')->only('store');
    }

    public function index()
    {
        $this->authorize('viewAny', Calificacion::class);

        return view('calificaciones.cliente.index', [
            'calificaciones' => $this->calificacionService->calificacionesCliente(auth()->user()),
            'resumenCalificaciones' => $this->calificacionService->resumenCliente(auth()->user()),
            'citasPendientesCalificacion' => $this->calificacionService->citasCompletadasSinCalificar(auth()->user()),
            'aspectosCalificacion' => $this->aspectoCalificacionService->aspectosActivos(),
        ]);
    }

    public function store(StoreCalificacionRequest $request)
    {
        $this->authorize('create', Calificacion::class);

        $this->calificacionService->createDesdeCliente(auth()->user(), $request->validated());

        return redirect()
            ->route('cliente.calificaciones.index')
            ->with('success', 'Calificación registrada correctamente.');
    }
}
