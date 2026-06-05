<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Services\CitaService;

class CitaController extends Controller
{
    public function __construct(
        protected CitaService $citaService
    ) {
        $this->middleware('permission:ver citas')->only('index');
        $this->middleware('permission:eliminar citas')->only('destroy');
    }

    public function index()
    {
        return view('admin.citas.index');
    }

    public function destroy(Cita $cita)
    {
        $this->authorize('delete', $cita);

        $this->citaService->cancelar($cita, 'Cita cancelada desde administracion');

        return back()->with('success', 'Cita cancelada correctamente.');
    }
}
