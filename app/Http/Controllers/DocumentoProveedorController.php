<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentoProveedorRequest;
use App\Http\Requests\UpdateDocumentoProveedorRequest;
use App\Models\DocumentoProveedor;
use App\Models\PerfilProveedor;
use App\Models\TipoDocumentoProveedor;
use App\Services\DocumentoProveedorService;

class DocumentoProveedorController extends Controller
{
    public function __construct(
        protected DocumentoProveedorService $documentoProveedorService
    ) {
        $this->middleware('permission:ver documentos proveedor')->only('index');
        $this->middleware('permission:crear documentos proveedor')->only(['create', 'store']);
        $this->middleware('permission:editar documentos proveedor')->only(['edit', 'update']);
        $this->middleware('permission:eliminar documentos proveedor')->only('destroy');
    }

    public function index()
    {
        return view('documentos-proveedor.index');
    }

    public function create()
    {
        return view('documentos-proveedor.create', $this->formData());
    }

    public function store(StoreDocumentoProveedorRequest $request)
    {
        $this->documentoProveedorService->create($request->validated());

        return redirect()
            ->route('documentos-proveedor.index')
            ->with('success', 'Documento del proveedor creado correctamente.');
    }

    public function edit(DocumentoProveedor $documentos_proveedor)
    {
        $documentos_proveedor->load('perfilProveedor.user', 'tipoDocumentoProveedor');

        return view('documentos-proveedor.edit', [
            ...$this->formData($documentos_proveedor),
            'documentoProveedor' => $documentos_proveedor,
        ]);
    }

    public function update(UpdateDocumentoProveedorRequest $request, DocumentoProveedor $documentos_proveedor)
    {
        $this->documentoProveedorService->update($documentos_proveedor, $request->validated());

        return redirect()
            ->route('documentos-proveedor.index')
            ->with('success', 'Documento del proveedor actualizado correctamente.');
    }

    public function destroy(DocumentoProveedor $documentos_proveedor)
    {
        $this->documentoProveedorService->toggleEstado($documentos_proveedor);

        $mensaje = $documentos_proveedor->estado
            ? 'Documento del proveedor activado correctamente.'
            : 'Documento del proveedor inactivado correctamente.';

        return redirect()
            ->route('documentos-proveedor.index')
            ->with('success', $mensaje);
    }

    protected function formData(?DocumentoProveedor $documentoProveedor = null): array
    {
        $perfilesProveedores = PerfilProveedor::with('user')
            ->where('estado', true)
            ->when($documentoProveedor, function ($query) use ($documentoProveedor) {
                $query->orWhere('id', $documentoProveedor->perfil_proveedor_id);
            })
            ->orderBy('nombre_publico')
            ->get();

        $tiposDocumentoProveedor = TipoDocumentoProveedor::query()
            ->where('estado', true)
            ->when($documentoProveedor, function ($query) use ($documentoProveedor) {
                $query->orWhere('id', $documentoProveedor->tipo_documento_proveedor_id);
            })
            ->orderBy('nombre')
            ->get();

        return compact('perfilesProveedores', 'tiposDocumentoProveedor');
    }
}
