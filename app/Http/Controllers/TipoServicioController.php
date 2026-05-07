<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTipoServicioRequest;
use App\Http\Requests\UpdateTipoServicioRequest;
use App\Models\Rubro;
use App\Models\TipoServicio;
use App\Services\TipoServicioService;

class TipoServicioController extends Controller
{
    public function __construct(
        protected TipoServicioService $tipoServicioService
    ) {
        $this->middleware('permission:ver tipos servicio')->only('index');
        $this->middleware('permission:crear tipos servicio')->only(['create', 'store']);
        $this->middleware('permission:editar tipos servicio')->only(['edit', 'update']);
        $this->middleware('permission:eliminar tipos servicio')->only('destroy');
    }

    public function index()
    {

        return view('tiposServicio.index');
    }

    public function create()
    {
        $rubros = Rubro::where('estado', true)->orderBy('nombre')->get();

        return view('tiposServicio.create', compact('rubros'));
    }

    public function store(StoreTipoServicioRequest $request)
    {
        $this->tipoServicioService->create($request->validated());

        return redirect()
            ->route('tipos-servicio.index')
            ->with('success', 'Tipo de servicio creado correctamente.');
    }

    public function edit(TipoServicio $tipos_servicio)
    {
        $rubros = Rubro::where('estado', true)->orderBy('nombre')->get();

        return view('tiposServicio.edit', [
            'tipoServicio' => $tipos_servicio->load('rubros'),
            'rubros' => $rubros,
        ]);
    }

    public function update(UpdateTipoServicioRequest $request, TipoServicio $tipos_servicio)
    {
        $this->tipoServicioService->update($tipos_servicio, $request->validated());

        return redirect()
            ->route('tipos-servicio.index')
            ->with('success', 'Tipo de servicio actualizado correctamente.');
    }

    public function destroy(TipoServicio $tipos_servicio)
    {
        $this->tipoServicioService->toggleEstado($tipos_servicio);

        $mensaje = $tipos_servicio->estado
            ? 'Tipo de servicio activado correctamente.'
            : 'Tipo de servicio inactivado correctamente.';

        return redirect()
            ->route('tipos-servicio.index')
            ->with('success', $mensaje);
    }
}
