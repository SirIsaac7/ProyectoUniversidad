<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedor\StoreCitaRequest;
use App\Http\Requests\Proveedor\UpdateCitaRequest;
use App\Models\Cita;
use App\Models\Solicitud;
use App\Services\CitaService;

class CitaController extends Controller
{
    public function __construct(
        protected CitaService $citaService
    ) {
        $this->middleware('permission:ver mis citas')->only('index');
        $this->middleware('permission:gestionar citas proveedor')->only(['store', 'update', 'cambiarEstado', 'destroy']);
    }

    public function index()
    {
        $perfilProveedor = auth()->user()->perfilProveedor;

        return response()->json(
            $perfilProveedor
                ? $this->citaService->citasProveedor($perfilProveedor)
                : collect()
        );
    }

    public function store(StoreCitaRequest $request)
    {
        $this->authorize('create', Cita::class);

        $solicitud = Solicitud::findOrFail($request->validated('solicitud_id'));

        $this->citaService->crearDesdeProveedor($solicitud, $request->validated());

        return back()->with('success', 'Cita programada correctamente.');
    }

    public function update(UpdateCitaRequest $request, Cita $cita)
    {
        $this->authorize('update', $cita);

        $this->citaService->actualizarDesdeProveedor($cita, $request->validated());

        return back()->with('success', 'Cita actualizada correctamente.');
    }

    public function cambiarEstado(UpdateCitaRequest $request, Cita $cita)
    {
        $this->authorize('update', $cita);

        $this->citaService->cambiarEstado(
            $cita,
            $request->validated('estado'),
            $request->validated('observaciones')
        );

        return back()->with('success', 'Estado de la cita actualizado correctamente.');
    }

    public function destroy(Cita $cita)
    {
        $this->authorize('delete', $cita);

        $this->citaService->cancelar($cita, 'Cita cancelada por el proveedor');

        return back()->with('success', 'Cita cancelada correctamente.');
    }
}
