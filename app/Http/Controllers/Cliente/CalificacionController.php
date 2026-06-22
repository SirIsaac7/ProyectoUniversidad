<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\StoreCalificacionRequest;
use App\Models\Calificacion;
use App\Services\AspectoCalificacionService;
use App\Services\CalificacionService;

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

        return view('cliente.calificaciones.index', [
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
