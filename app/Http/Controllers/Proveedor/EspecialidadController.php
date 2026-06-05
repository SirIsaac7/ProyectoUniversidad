<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiPerfilProveedor\StoreMiProveedorEspecialidadRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiProveedorEspecialidadRequest;
use App\Models\ProveedorEspecialidad;
use App\Services\MiPerfilProveedor\EspecialidadService;

class EspecialidadController extends Controller
{
    public function __construct(
        protected EspecialidadService $especialidadService
    ) {
        $this->middleware('permission:gestionar especialidades proveedor');
    }

    public function index()
    {
        return view('proveedor.especialidades.index', $this->especialidadService->obtenerDatosVista());
    }

    public function store(StoreMiProveedorEspecialidadRequest $request)
    {
        $this->especialidadService->asignarActual($request->validated());

        return back()->with('success', 'Especialidad agregada correctamente.');
    }

    public function update(UpdateMiProveedorEspecialidadRequest $request, ProveedorEspecialidad $proveedorEspecialidad)
    {
        $this->especialidadService->actualizarActual($proveedorEspecialidad, $request->validated());

        return back()->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(ProveedorEspecialidad $proveedorEspecialidad)
    {
        $this->especialidadService->eliminarActual($proveedorEspecialidad);

        return back()->with('success', 'Especialidad retirada correctamente.');
    }

    public function activar(ProveedorEspecialidad $proveedorEspecialidad)
    {
        $this->especialidadService->activarActual($proveedorEspecialidad);

        return back()->with('success', 'Especialidad activada correctamente.');
    }
}
