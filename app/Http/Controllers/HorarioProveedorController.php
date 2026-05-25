<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHorarioProveedorRequest;
use App\Http\Requests\UpdateHorarioProveedorRequest;
use App\Models\HorarioProveedor;
use App\Models\PerfilProveedor;
use App\Services\HorarioProveedorService;

class HorarioProveedorController extends Controller
{
    public function __construct(
        protected HorarioProveedorService $horarioProveedorService
    ) {
        $this->middleware('permission:ver horarios proveedor')->only('index');
        $this->middleware('permission:crear horarios proveedor')->only(['create', 'store']);
        $this->middleware('permission:editar horarios proveedor')->only(['edit', 'update']);
        $this->middleware('permission:eliminar horarios proveedor')->only('destroy');
    }

    public function index()
    {
        return view('horarios-proveedor.index');
    }

    public function create()
    {
        return view('horarios-proveedor.create', [
            'perfilesProveedores' => $this->perfilesProveedores(),
        ]);
    }

    public function store(StoreHorarioProveedorRequest $request)
    {
        $this->horarioProveedorService->create($request->validated());

        return redirect()
            ->route('horarios-proveedor.index')
            ->with('success', 'Horario creado correctamente.');
    }

    public function edit($horarios_proveedor)
    {
        $horarioProveedor = HorarioProveedor::with('perfilProveedor.user')->findOrFail($horarios_proveedor);

        return view('horarios-proveedor.edit', [
            'horarioProveedor' => $horarioProveedor,
            'perfilesProveedores' => $this->perfilesProveedores($horarioProveedor),
        ]);
    }

    public function update(UpdateHorarioProveedorRequest $request, $horarios_proveedor)
    {
        $horarioProveedor = HorarioProveedor::findOrFail($horarios_proveedor);

        $this->horarioProveedorService->update($horarioProveedor, $request->validated());

        return redirect()
            ->route('horarios-proveedor.index')
            ->with('success', 'Horario actualizado correctamente.');
    }

    public function destroy($horarios_proveedor)
    {
        $horarioProveedor = HorarioProveedor::findOrFail($horarios_proveedor);

        $this->horarioProveedorService->toggleEstado($horarioProveedor);

        $mensaje = $horarioProveedor->estado
            ? 'Horario activado correctamente.'
            : 'Horario inactivado correctamente.';

        return redirect()
            ->route('horarios-proveedor.index')
            ->with('success', $mensaje);
    }

    protected function perfilesProveedores(?HorarioProveedor $horarioProveedor = null)
    {
        return PerfilProveedor::with('user')
            ->where('estado', true)
            ->where(function ($query) {
                $query->where('estado_verificacion', 'aprobado')
                    ->orWhere('estado_verificacion', 'pendiente');
            })
            ->when($horarioProveedor, fn ($query) => $query->orWhere('id', $horarioProveedor->perfil_proveedor_id))
            ->orderBy('nombre_publico')
            ->get();
    }
}
