<?php

namespace App\Http\Controllers\Proveedores\Administracion;

use App\Http\Controllers\Controller;

use App\Http\Requests\Proveedores\Administracion\StoreTipoDocumentoProveedorRequest;
use App\Http\Requests\Proveedores\Administracion\UpdateTipoDocumentoProveedorRequest;
use App\Models\TipoDocumentoProveedor;
use App\Services\Proveedores\TipoDocumentoProveedorService;

class TipoDocumentoProveedorController extends Controller
{
    public function __construct(
        protected TipoDocumentoProveedorService $tipoDocumentoProveedorService
    ) {
        $this->middleware('permission:ver tipos documento proveedor')->only('index');
        $this->middleware('permission:crear tipos documento proveedor')->only(['create', 'store']);
        $this->middleware('permission:editar tipos documento proveedor')->only(['edit', 'update']);
        $this->middleware('permission:eliminar tipos documento proveedor')->only('destroy');
    }

    public function index()
    {
        return view('proveedores.administracion.tipos-documento.index');
    }

    public function create()
    {
        return view('proveedores.administracion.tipos-documento.create');
    }

    public function store(StoreTipoDocumentoProveedorRequest $request)
    {
        $this->tipoDocumentoProveedorService->create($request->validated());

        return redirect()
            ->route('tipos-documento-proveedor.index')
            ->with('success', 'Tipo de documento creado correctamente.');
    }

    public function edit(TipoDocumentoProveedor $tipos_documento_proveedor)
    {
        return view('proveedores.administracion.tipos-documento.edit', [
            'tipoDocumentoProveedor' => $tipos_documento_proveedor,
        ]);
    }

    public function update(UpdateTipoDocumentoProveedorRequest $request, TipoDocumentoProveedor $tipos_documento_proveedor)
    {
        $this->tipoDocumentoProveedorService->update($tipos_documento_proveedor, $request->validated());

        return redirect()
            ->route('tipos-documento-proveedor.index')
            ->with('success', 'Tipo de documento actualizado correctamente.');
    }

    public function destroy(TipoDocumentoProveedor $tipos_documento_proveedor)
    {
        $this->tipoDocumentoProveedorService->toggleEstado($tipos_documento_proveedor);

        $mensaje = $tipos_documento_proveedor->estado
            ? 'Tipo de documento activado correctamente.'
            : 'Tipo de documento inactivado correctamente.';

        return redirect()
            ->route('tipos-documento-proveedor.index')
            ->with('success', $mensaje);
    }
}
