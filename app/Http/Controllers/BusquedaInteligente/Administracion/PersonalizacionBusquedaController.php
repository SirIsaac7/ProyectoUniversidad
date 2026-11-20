<?php

namespace App\Http\Controllers\BusquedaInteligente\Administracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusquedaInteligente\Administracion\UpdatePersonalizacionBusquedaRequest;
use App\Services\BusquedaInteligente\Administracion\ConfiguracionBusquedaVisualService;

class PersonalizacionBusquedaController extends Controller
{
    public function __construct(
        protected ConfiguracionBusquedaVisualService $configuracionBusquedaVisualService,
    ) {
        $this->middleware('permission:ver panel motor busqueda')->only('edit');
        $this->middleware('permission:ver personalizacion busqueda')->only('edit');
        $this->middleware('permission:modificar personalizacion busqueda')->only('update');
        $this->middleware('permission:restaurar personalizacion busqueda')->only('destroy');
    }

    public function edit()
    {
        return view('busqueda-inteligente.administracion.personalizacion.edit', [
            'personalizacionBusqueda' => $this->configuracionBusquedaVisualService->configuracion(),
            'personalizacionCliente' => $this->configuracionBusquedaVisualService->paraCliente(),
            'fasesBusqueda' => ConfiguracionBusquedaVisualService::FASES,
            'familiasBusqueda' => $this->configuracionBusquedaVisualService->familiasTipograficas(),
        ]);
    }

    public function update(UpdatePersonalizacionBusquedaRequest $request)
    {
        $this->configuracionBusquedaVisualService->actualizar($request->validated());

        return redirect()
            ->route('motor-busqueda.personalizacion.edit')
            ->with('success', 'Personalización de búsqueda actualizada correctamente.');
    }

    public function destroy()
    {
        $this->configuracionBusquedaVisualService->restaurar();

        return redirect()
            ->route('motor-busqueda.personalizacion.edit')
            ->with('success', 'Se restauraron los valores predeterminados de la búsqueda.');
    }
}
