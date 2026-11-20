<?php

namespace App\Http\Controllers\BusquedaInteligente\Administracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusquedaInteligente\Administracion\UpdateProgramacionIndexacionRequest;
use App\Services\BusquedaInteligente\Administracion\ConfiguracionIndexacionMotorService;

class ProgramacionIndexacionController extends Controller
{
    public function __construct(
        protected ConfiguracionIndexacionMotorService $configuracionIndexacionService,
    ) {
        $this->middleware('permission:ver panel motor busqueda')->only('edit');
        $this->middleware('permission:configurar programacion indexaciones motor')->only(['edit', 'update']);
    }

    public function edit()
    {
        return view('busqueda-inteligente.administracion.programacion.edit', [
            'configuracion' => $this->configuracionIndexacionService->configuracion(),
            'proximaEjecucion' => $this->configuracionIndexacionService->proximaEjecucion(),
        ]);
    }

    public function update(UpdateProgramacionIndexacionRequest $request)
    {
        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');
        $this->configuracionIndexacionService->actualizar($data);

        return redirect()
            ->route('motor-busqueda.programacion.edit')
            ->with('success', 'Programación de indexación actualizada correctamente.');
    }
}
