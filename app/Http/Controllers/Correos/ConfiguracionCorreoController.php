<?php

namespace App\Http\Controllers\Correos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Correos\UpdateConfiguracionCorreoRequest;
use App\Services\Correos\ConfiguracionCorreoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConfiguracionCorreoController extends Controller
{
    public function __construct(
        protected ConfiguracionCorreoService $configuracionCorreoService
    ) {
        $this->middleware('permission:ver configuracion correos')->only('edit');
        $this->middleware('permission:editar configuracion correos')->only('update');
    }

    public function edit(): View
    {
        return view('correos.index', [
            'configuracion' => $this->configuracionCorreoService->configuracion(),
        ]);
    }

    public function update(UpdateConfiguracionCorreoRequest $request): RedirectResponse
    {
        $this->configuracionCorreoService->actualizar($request->validated());

        return redirect()
            ->route('configuracion-correos.edit')
            ->with('success', 'Configuracion de correos actualizada correctamente.');
    }
}
