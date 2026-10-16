<?php

namespace App\Http\Controllers\Proveedores\Administracion;

use App\Http\Controllers\Controller;

use App\Http\Requests\Proveedores\Administracion\StoreUbicacionProveedorRequest;
use App\Http\Requests\Proveedores\Administracion\UpdateUbicacionProveedorRequest;
use App\Models\PerfilProveedor;
use App\Models\UbicacionProveedor;
use App\Services\Proveedores\UbicacionProveedorService;

class UbicacionProveedorController extends Controller
{
    public function __construct(
        protected UbicacionProveedorService $ubicacionProveedorService
    ) {
        $this->middleware('permission:ver ubicaciones proveedor')->only('index');
        $this->middleware('permission:crear ubicaciones proveedor')->only(['create', 'store']);
        $this->middleware('permission:editar ubicaciones proveedor')->only(['edit', 'update']);
        $this->middleware('permission:eliminar ubicaciones proveedor')->only('destroy');
    }

    public function index()
    {
        return view('proveedores.administracion.ubicaciones.index');
    }

    public function create()
    {
        $perfilesProveedores = PerfilProveedor::with('user')
            ->where('estado', true)
            ->whereDoesntHave('ubicacion')
            ->orderBy('nombre_publico')
            ->get();

        return view('proveedores.administracion.ubicaciones.create', compact('perfilesProveedores'));
    }

    public function store(StoreUbicacionProveedorRequest $request)
    {
        $this->ubicacionProveedorService->create($request->validated());

        return redirect()
            ->route('ubicaciones-proveedor.index')
            ->with('success', 'Ubicacion creada correctamente.');
    }

    public function edit($ubicaciones_proveedor)
    {
        $ubicacionProveedor = UbicacionProveedor::with('perfilProveedor.user')->findOrFail($ubicaciones_proveedor);

        $perfilesProveedores = PerfilProveedor::with('user')
            ->where('estado', true)
            ->where(function ($query) use ($ubicacionProveedor) {
                $query->whereDoesntHave('ubicacion')
                    ->orWhere('id', $ubicacionProveedor->perfil_proveedor_id);
            })
            ->orderBy('nombre_publico')
            ->get();

        return view('proveedores.administracion.ubicaciones.edit', compact('ubicacionProveedor', 'perfilesProveedores'));
    }

    public function update(UpdateUbicacionProveedorRequest $request, $ubicaciones_proveedor)
    {
        $ubicacionProveedor = UbicacionProveedor::findOrFail($ubicaciones_proveedor);

        $this->ubicacionProveedorService->update($ubicacionProveedor, $request->validated());

        return redirect()
            ->route('ubicaciones-proveedor.index')
            ->with('success', 'Ubicacion actualizada correctamente.');
    }

    public function destroy($ubicaciones_proveedor)
    {
        $ubicacionProveedor = UbicacionProveedor::findOrFail($ubicaciones_proveedor);

        $this->ubicacionProveedorService->delete($ubicacionProveedor);

        return redirect()
            ->route('ubicaciones-proveedor.index')
            ->with('success', 'Ubicacion eliminada correctamente.');
    }
}
