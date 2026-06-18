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
        $this->middleware('permission:crear mis citas proveedor')->only('store');
        $this->middleware('permission:editar mis citas proveedor')->only(['update', 'cambiarEstado']);
        $this->middleware('permission:cancelar mis citas proveedor')->only('destroy');
    }

    public function index()
    {
        return redirect()->route('proveedor.solicitudes.index', ['tab' => 'citas']);
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
