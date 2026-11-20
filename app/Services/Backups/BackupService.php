<?php

namespace App\Services\Backups;

use App\Models\ConfiguracionBackup;
use App\Services\Correos\ConfiguracionCorreoService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService
{
    public function __construct(
        protected ConfiguracionCorreoService $configuracionCorreoService
    ) {}

    public function configuracion(): ConfiguracionBackup
    {
        return ConfiguracionBackup::firstOrCreate(
            ['id' => 1],
            [
                'hora_ejecucion' => '02:00:00',
                'frecuencia' => 'diario',
                'activo' => true,
            ]
        );
    }

    public function ejecutarManual(): array
    {
        $configuracion = $this->configuracion();

        try {
            $directorioTemporal = config('backup.backup.temporary_directory');

            File::deleteDirectory($directorioTemporal.DIRECTORY_SEPARATOR.'temp');
            File::deleteDirectory($directorioTemporal);
            $phpBinary = '"'.PHP_BINARY.'"';
            $artisan = '"'.base_path('artisan').'"';
            $comando = $phpBinary.' '.$artisan.' backup:run --disable-notifications 2>&1';

            $salida = [];
            $codigoSalida = 1;

            exec($comando, $salida, $codigoSalida);

            $mensaje = trim(implode(PHP_EOL, $salida)) ?: 'Backup ejecutado correctamente.';

            if ($codigoSalida !== 0) {
                $configuracion->update([
                    'ultimo_backup_at' => now(),
                    'ultimo_estado' => 'error',
                    'ultimo_mensaje' => $mensaje,
                ]);

                $this->configuracionCorreoService->notificarResultadoBackup(false, $mensaje);

                return ['ok' => false, 'mensaje' => $mensaje];
            }

            $configuracion->update([
                'ultimo_backup_at' => now(),
                'ultimo_estado' => 'correcto',
                'ultimo_mensaje' => $mensaje,
            ]);

            $this->configuracionCorreoService->notificarResultadoBackup(
                true,
                $mensaje,
                $this->rutaBackupMasReciente()
            );

            return ['ok' => true, 'mensaje' => $mensaje];
        } catch (Throwable $e) {
            $configuracion->update([
                'ultimo_backup_at' => now(),
                'ultimo_estado' => 'error',
                'ultimo_mensaje' => $e->getMessage(),
            ]);

            $this->configuracionCorreoService->notificarResultadoBackup(false, $e->getMessage());

            return ['ok' => false, 'mensaje' => $e->getMessage()];
        }
    }

    public function backupsLocales(): array
    {
        return collect(Storage::disk('backups')->allFiles())
            ->filter(fn ($archivo) => str_ends_with($archivo, '.zip'))
            ->map(fn ($archivo) => [
                'nombre' => basename($archivo),
                'ruta' => $archivo,
                'tamano' => Storage::disk('backups')->size($archivo),
                'fecha' => Storage::disk('backups')->lastModified($archivo),
            ])
            ->sortByDesc('fecha')
            ->values()
            ->all();
    }

    private function rutaBackupMasReciente(): ?string
    {
        $disco = Storage::disk('backups');
        $archivo = collect($disco->allFiles())
            ->filter(fn ($ruta) => str_ends_with($ruta, '.zip'))
            ->sortByDesc(fn ($ruta) => $disco->lastModified($ruta))
            ->first();

        return $archivo ? $disco->path($archivo) : null;
    }
}
