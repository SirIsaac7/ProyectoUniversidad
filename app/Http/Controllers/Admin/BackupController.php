<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBackupRequest;
use App\Services\BackupService;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backupService
    ) {
        $this->middleware('permission:ver backups')->only('index');
        $this->middleware('permission:configurar backups')->only('update');
        $this->middleware('permission:ejecutar backups')->only('run');
    }

    public function index()
    {
        return view('admin.backups.index', [
            'configuracion' => $this->backupService->configuracion(),
            'backups' => $this->backupService->backupsLocales(),
        ]);
    }

    public function update(UpdateBackupRequest $request)
    {
        $data = $request->validated();

        $this->backupService->configuracion()->update([
            'hora_ejecucion' => $data['hora_ejecucion'],
            'frecuencia' => $data['frecuencia'],
            'dia_semana' => $data['frecuencia'] === 'semanal' ? $data['dia_semana'] : null,
            'dia_mes' => $data['frecuencia'] === 'mensual' ? $data['dia_mes'] : null,
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('success', 'Configuracion de backups actualizada correctamente.');
    }

    public function run()
    {
        $resultado = $this->backupService->ejecutarManual();

        return back()->with(
            $resultado['ok'] ? 'success' : 'error',
            $resultado['mensaje']
        );
    }
}
