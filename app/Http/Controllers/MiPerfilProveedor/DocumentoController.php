<?php

namespace App\Http\Controllers\MiPerfilProveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiPerfilProveedor\StoreMiDocumentoProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiDocumentoProveedorRequest;
use App\Models\DocumentoProveedor;
use App\Services\MiPerfilProveedor\DocumentoService;

class DocumentoController extends Controller
{
    public function __construct(
        protected DocumentoService $documentoService
    ) {
        $this->middleware('permission:gestionar documentos proveedor');
    }

    public function index()
    {
        return view('mi-perfil-proveedor.documentos.index', [
            'perfilProveedor' => $this->documentoService->getPerfilActual(),
            'tiposDocumentoDisponibles' => $this->documentoService->getTiposDocumentoDisponibles(),
        ]);
    }

    public function store(StoreMiDocumentoProveedorRequest $request)
    {
        $this->documentoService->subirActual($request->validated());

        return back()->with('success', 'Documento subido correctamente. Queda pendiente de revision.');
    }

    public function update(UpdateMiDocumentoProveedorRequest $request, DocumentoProveedor $documentoProveedor)
    {
        $this->documentoService->actualizarActual($documentoProveedor, $request->validated());

        return back()->with('success', 'Documento actualizado correctamente. Queda pendiente de nueva revision.');
    }

    public function destroy(DocumentoProveedor $documentoProveedor)
    {
        $this->documentoService->eliminarActual($documentoProveedor);

        return back()->with('success', 'Documento retirado correctamente.');
    }
}
