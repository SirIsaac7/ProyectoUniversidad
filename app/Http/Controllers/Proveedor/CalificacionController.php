<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedor\StoreRespuestaCalificacionRequest;
use App\Http\Requests\Proveedor\UpdateRespuestaCalificacionRequest;
use App\Models\Calificacion;
use App\Models\RespuestaCalificacion;
use App\Services\CalificacionService;
use App\Services\RespuestaCalificacionService;

class CalificacionController extends Controller
{
    public function __construct(
        protected CalificacionService $calificacionService,
        protected RespuestaCalificacionService $respuestaCalificacionService
    ) {
        $this->middleware('permission:ver mis calificaciones proveedor')->only('index');
        $this->middleware('permission:responder mis calificaciones proveedor')->only('storeRespuesta');
        $this->middleware('permission:responder mis calificaciones proveedor')->only('updateRespuesta');
    }

    public function index()
    {
        $this->authorize('viewAny', Calificacion::class);

        return view('proveedor.calificaciones.index', [
            'calificaciones' => $this->calificacionService->calificacionesProveedor(auth()->user()),
            'resumenCalificaciones' => $this->calificacionService->resumenProveedor(auth()->user()),
            'respuestasPendientes' => $this->calificacionService->respuestasPendientesProveedor(auth()->user()),
        ]);
    }

    public function storeRespuesta(StoreRespuestaCalificacionRequest $request)
    {
        $calificacion = Calificacion::with('respuesta', 'cita.solicitud')->findOrFail($request->validated('calificacion_id'));

        $this->authorize('create', [RespuestaCalificacion::class, $calificacion]);

        $this->respuestaCalificacionService->createDesdeProveedor(auth()->user(), $calificacion, $request->validated());

        return redirect()
            ->route('proveedor.calificaciones.index')
            ->with('success', 'Respuesta registrada correctamente.');
    }

    public function updateRespuesta(UpdateRespuestaCalificacionRequest $request, RespuestaCalificacion $respuestaCalificacion)
    {
        $this->authorize('update', $respuestaCalificacion);

        $this->respuestaCalificacionService->update($respuestaCalificacion, $request->validated());

        return redirect()
            ->route('proveedor.calificaciones.index')
            ->with('success', 'Respuesta actualizada correctamente.');
    }

}
