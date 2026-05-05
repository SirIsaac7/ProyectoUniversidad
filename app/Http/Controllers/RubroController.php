<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRubroRequest;
use App\Http\Requests\UpdateRubroRequest;
use App\Models\Rubro;
use App\Services\RubroService;

class RubroController extends Controller
{
    public function __construct(
        protected RubroService $rubroService
    ) {
        $this->middleware('permission:ver rubros')->only('index');
        $this->middleware('permission:crear rubros')->only(['create', 'store']);
        $this->middleware('permission:editar rubros')->only(['edit', 'update']);
        $this->middleware('permission:eliminar rubros')->only('destroy');
    }

    public function index()
    {
        return view('rubros.index');
    }

    public function create()
    {
        return view('rubros.create');
    }

    public function store(StoreRubroRequest $request)
    {
        $this->rubroService->create($request->validated());

        return redirect()
            ->route('rubros.index')
            ->with('success', 'Rubro creado correctamente.');
    }

    public function edit(Rubro $rubro)
    {
        return view('rubros.edit', compact('rubro'));
    }

    public function update(UpdateRubroRequest $request, Rubro $rubro)
    {
        $this->rubroService->update($rubro, $request->validated());

        return redirect()
            ->route('rubros.index')
            ->with('success', 'Rubro actualizado correctamente.');
    }

    public function destroy(Rubro $rubro)
    {
        $this->rubroService->toggleEstado($rubro);

        $mensaje = $rubro->estado
            ? 'Rubro activado correctamente.'
            : 'Rubro inactivado correctamente.';

        return redirect()
            ->route('rubros.index')
            ->with('success', $mensaje);
    }
}
