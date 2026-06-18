<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dev', function () {
    $this->info('Iniciando entorno de desarrollo (Serve, Reverb y Queue)...');
    $this->info('Presiona Ctrl+C para apagar todos los servicios.');

    $pool = Process::pool(function ($pool) {
        $pool->path(base_path())->command('php artisan serve');
        $pool->path(base_path())->command('php artisan reverb:start');
        $pool->path(base_path())->command('php artisan queue:work');
    })->start(function ($type, $output) {

        echo $output;
    });

    while ($pool->running()->count() > 0) {
        sleep(1);
    }
})->purpose('Arranca todo el entorno de desarrollo a la vez (Serve, Reverb y Queue)');
