<?php

namespace App\Http\Controllers\MiPerfilProveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiPerfilProveedor\StoreMiDocumentoProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\StoreMiHorarioProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\StoreMiPortafolioProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\StoreMiProveedorEspecialidadRequest;
use App\Http\Requests\MiPerfilProveedor\StoreMiUbicacionProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiDocumentoProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiHorarioProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiPerfilProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiPortafolioProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiProveedorEspecialidadRequest;
use App\Models\DocumentoProveedor;
use App\Models\HorarioProveedor;
use App\Models\PortafolioProveedor;
use App\Models\ProveedorEspecialidad;
use App\Services\MiPerfilProveedor\MiPerfilProveedorService;

class MiPerfilProveedorController extends Controller
{
    public function __construct(
        protected MiPerfilProveedorService $miPerfilProveedorService
    ) {
        $this->middleware('permission:visualizar perfil proveedor')->only('index');
        $this->middleware('permission:actualizar perfil proveedor')->only('update');
        $this->middleware('permission:gestionar especialidades proveedor')->only([
            'storeEspecialidad',
            'updateEspecialidad',
            'destroyEspecialidad',
        ]);
        $this->middleware('permission:gestionar horarios proveedor')->only([
            'storeHorario',
            'updateHorario',
            'destroyHorario',
        ]);
        $this->middleware('permission:gestionar ubicacion proveedor')->only('storeUbicacion');
        $this->middleware('permission:gestionar portafolio proveedor')->only([
            'storePortafolio',
            'updatePortafolio',
            'destroyPortafolio',
        ]);
        $this->middleware('permission:gestionar documentos proveedor')->only([
            'storeDocumento',
            'updateDocumento',
            'destroyDocumento',
        ]);
    }

    public function index()
    {
        $perfilProveedor = $this->miPerfilProveedorService->getPerfilActual();
        $especialidadesDisponibles = $this->miPerfilProveedorService->getEspecialidadesDisponibles();
        $tiposDocumentoDisponibles = $this->miPerfilProveedorService->getTiposDocumentoDisponibles();

        return view('mi-perfil-proveedor.index', compact(
            'perfilProveedor',
            'especialidadesDisponibles',
            'tiposDocumentoDisponibles'
        ));
    }

    public function update(UpdateMiPerfilProveedorRequest $request)
    {
        $this->miPerfilProveedorService->updatePerfilActual($request->validated());

        return redirect()
            ->route('mi-perfil-proveedor.index')
            ->with('success', 'Tu perfil de proveedor fue actualizado correctamente.');
    }

    public function storeEspecialidad(StoreMiProveedorEspecialidadRequest $request)
    {
        $this->miPerfilProveedorService->asignarEspecialidadActual($request->validated());

        return back()->with('success', 'Especialidad agregada correctamente.');
    }

    public function updateEspecialidad(UpdateMiProveedorEspecialidadRequest $request, ProveedorEspecialidad $proveedorEspecialidad)
    {
        $this->miPerfilProveedorService->actualizarEspecialidadActual($proveedorEspecialidad, $request->validated());

        return back()->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroyEspecialidad(ProveedorEspecialidad $proveedorEspecialidad)
    {
        $this->miPerfilProveedorService->eliminarEspecialidadActual($proveedorEspecialidad);

        return back()->with('success', 'Especialidad retirada correctamente.');
    }

    public function storeHorario(StoreMiHorarioProveedorRequest $request)
    {
        $this->miPerfilProveedorService->crearHorarioActual($request->validated());

        return back()->with('success', 'Horario agregado correctamente.');
    }

    public function updateHorario(UpdateMiHorarioProveedorRequest $request, HorarioProveedor $horarioProveedor)
    {
        $this->miPerfilProveedorService->actualizarHorarioActual($horarioProveedor, $request->validated());

        return back()->with('success', 'Horario actualizado correctamente.');
    }

    public function destroyHorario(HorarioProveedor $horarioProveedor)
    {
        $this->miPerfilProveedorService->eliminarHorarioActual($horarioProveedor);

        return back()->with('success', 'Horario retirado correctamente.');
    }

    public function storeUbicacion(StoreMiUbicacionProveedorRequest $request)
    {
        $this->miPerfilProveedorService->guardarUbicacionActual($request->validated());

        return back()->with('success', 'Ubicacion guardada correctamente.');
    }

    public function storePortafolio(StoreMiPortafolioProveedorRequest $request)
    {
        $this->miPerfilProveedorService->crearPortafolioActual($request->validated());

        return back()->with('success', 'Trabajo agregado al portafolio correctamente.');
    }

    public function updatePortafolio(UpdateMiPortafolioProveedorRequest $request, PortafolioProveedor $portafolioProveedor)
    {
        $this->miPerfilProveedorService->actualizarPortafolioActual($portafolioProveedor, $request->validated());

        return back()->with('success', 'Trabajo actualizado correctamente.');
    }

    public function destroyPortafolio(PortafolioProveedor $portafolioProveedor)
    {
        $this->miPerfilProveedorService->eliminarPortafolioActual($portafolioProveedor);

        return back()->with('success', 'Trabajo retirado del portafolio correctamente.');
    }

    public function storeDocumento(StoreMiDocumentoProveedorRequest $request)
    {
        $this->miPerfilProveedorService->subirDocumentoActual($request->validated());

        return back()->with('success', 'Documento subido correctamente. Queda pendiente de revision.');
    }

    public function updateDocumento(UpdateMiDocumentoProveedorRequest $request, DocumentoProveedor $documentoProveedor)
    {
        $this->miPerfilProveedorService->actualizarDocumentoActual($documentoProveedor, $request->validated());

        return back()->with('success', 'Documento actualizado correctamente. Queda pendiente de nueva revision.');
    }

    public function destroyDocumento(DocumentoProveedor $documentoProveedor)
    {
        $this->miPerfilProveedorService->eliminarDocumentoActual($documentoProveedor);

        return back()->with('success', 'Documento retirado correctamente.');
    }
}
