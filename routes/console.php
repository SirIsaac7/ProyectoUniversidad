<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use App\Models\ConfiguracionBackup;
use App\Services\BackupService;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dev', function () {
    $this->info('Iniciando entorno de desarrollo (Serve, Reverb, Queue y Schedule)...');
    $this->info('Presiona Ctrl+C para apagar todos los servicios.');

    $pool = Process::pool(function ($pool) {
        $pool->path(base_path())->command('php artisan serve');
        $pool->path(base_path())->command('php artisan reverb:start');
        $pool->path(base_path())->command('php artisan queue:work');
        $pool->path(base_path())->command('php artisan schedule:work');
    })->start(function ($type, $output) {

        echo $output;
    });

    while ($pool->running()->count() > 0) {
        sleep(1);
    }
})->purpose('Arranca todo el entorno de desarrollo a la vez (Serve, Reverb, Queue y Schedule)');

Schedule::call(function () {
    app(BackupService::class)->ejecutarManual();
})
    ->everyMinute()
    ->name('backup-automatico')
    ->withoutOverlapping()
    ->when(function () {
        $configuracion = ConfiguracionBackup::first();

        if (! $configuracion || ! $configuracion->activo) {
            return false;
        }

        $horaActual = now()->format('H:i');
        $horaBackup = substr($configuracion->hora_ejecucion, 0, 5);

        if ($horaActual !== $horaBackup) {
            return false;
        }

        return match ($configuracion->frecuencia) {
            'diario' => true,
            'semanal' => (int) $configuracion->dia_semana === now()->dayOfWeekIso,
            'mensual' => (int) $configuracion->dia_mes === now()->day,
            default => false,
        };
    });
