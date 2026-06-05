<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\StorePortafolioProveedorRequest;
use App\Http\Requests\UpdatePortafolioProveedorRequest;
use App\Models\PerfilProveedor;
use App\Models\PortafolioProveedor;
use App\Services\PortafolioProveedorService;

class PortafolioProveedorController extends Controller
{
    public function __construct(
        protected PortafolioProveedorService $portafolioProveedorService
    ) {
        $this->middleware('permission:ver portafolio proveedor')->only('index');
        $this->middleware('permission:crear portafolio proveedor')->only(['create', 'store']);
        $this->middleware('permission:editar portafolio proveedor')->only(['edit', 'update']);
        $this->middleware('permission:eliminar portafolio proveedor')->only('destroy');
    }

    public function index()
    {
        return view('admin.portafolio-proveedor.index');
    }

    public function create()
    {
        return view('admin.portafolio-proveedor.create', [
            'perfilesProveedores' => $this->perfilesProveedores(),
        ]);
    }

    public function store(StorePortafolioProveedorRequest $request)
    {
        $this->portafolioProveedorService->create($request->validated());

        return redirect()
            ->route('portafolio-proveedor.index')
            ->with('success', 'Trabajo de portafolio creado correctamente.');
    }

    public function edit($portafolio_proveedor)
    {
        $portafolioProveedor = PortafolioProveedor::with('perfilProveedor.user', 'imagenes')->findOrFail($portafolio_proveedor);

        return view('admin.portafolio-proveedor.edit', [
            'portafolioProveedor' => $portafolioProveedor,
            'perfilesProveedores' => $this->perfilesProveedores($portafolioProveedor),
        ]);
    }

    public function update(UpdatePortafolioProveedorRequest $request, $portafolio_proveedor)
    {
        $portafolioProveedor = PortafolioProveedor::with('imagenes')->findOrFail($portafolio_proveedor);

        $this->portafolioProveedorService->update($portafolioProveedor, $request->validated());

        return redirect()
            ->route('portafolio-proveedor.index')
            ->with('success', 'Trabajo de portafolio actualizado correctamente.');
    }

    public function destroy($portafolio_proveedor)
    {
        $portafolioProveedor = PortafolioProveedor::findOrFail($portafolio_proveedor);

        $this->portafolioProveedorService->toggleEstado($portafolioProveedor);

        $mensaje = $portafolioProveedor->estado
            ? 'Trabajo de portafolio activado correctamente.'
            : 'Trabajo de portafolio inactivado correctamente.';

        return redirect()
            ->route('portafolio-proveedor.index')
            ->with('success', $mensaje);
    }

    protected function perfilesProveedores(?PortafolioProveedor $portafolioProveedor = null)
    {
        return PerfilProveedor::with('user')
            ->where('estado', true)
            ->where(function ($query) {
                $query->where('estado_verificacion', 'aprobado')
                    ->orWhere('estado_verificacion', 'pendiente');
            })
            ->when($portafolioProveedor, fn ($query) => $query->orWhere('id', $portafolioProveedor->perfil_proveedor_id))
            ->orderBy('nombre_publico')
            ->get();
    }
}
