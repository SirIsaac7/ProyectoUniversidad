<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiPerfilProveedor\StoreMiHorarioProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiHorarioProveedorRequest;
use App\Models\HorarioProveedor;
use App\Services\MiPerfilProveedor\HorarioService;

class HorarioController extends Controller
{
    public function __construct(
        protected HorarioService $horarioService
    ) {
        $this->middleware('permission:gestionar horarios proveedor');
    }

    public function index()
    {
        return view('proveedor.horarios.index', $this->horarioService->obtenerDatosVista());
    }

    public function store(StoreMiHorarioProveedorRequest $request)
    {
        $this->horarioService->crearActual($request->validated());

        return back()->with('success', 'Horario agregado correctamente.');
    }

    public function update(UpdateMiHorarioProveedorRequest $request, HorarioProveedor $horarioProveedor)
    {
        $this->horarioService->actualizarActual($horarioProveedor, $request->validated());

        return back()->with('success', 'Horario actualizado correctamente.');
    }

    public function destroy(HorarioProveedor $horarioProveedor)
    {
        $this->horarioService->eliminarActual($horarioProveedor);

        return back()->with('success', 'Horario retirado correctamente.');
    }
}
