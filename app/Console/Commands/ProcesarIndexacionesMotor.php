<?php

namespace App\Console\Commands;

use App\Exceptions\MotorBusquedaException;
use App\Services\Admin\ConfiguracionIndexacionMotorService;
use App\Services\Admin\MotorIndexacionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcesarIndexacionesMotor extends Command
{
    protected $signature = 'motor:indexaciones-procesar';

    protected $description = 'Inicia indexaciones programadas y actualiza las ejecuciones del motor en curso';

    public function handle(
        MotorIndexacionService $motorIndexacionService,
        ConfiguracionIndexacionMotorService $configuracionIndexacionService,
    ): int {
        try {
            $seguimiento = $motorIndexacionService->actualizarEnCurso();
            $this->line("Seguimiento: {$seguimiento['actualizadas']} actualizadas, {$seguimiento['fallidas']} fallidas.");

            if (! $configuracionIndexacionService->reclamarEjecucionProgramada()) {
                return self::SUCCESS;
            }

            try {
                $ejecucion = $motorIndexacionService->iniciarAutomatica();
                $this->info('Indexacion automatica iniciada: '.$ejecucion->fastapi_identificador);
            } catch (MotorBusquedaException $exception) {
                if ($exception->tipo === 'conflicto') {
                    $motorIndexacionService->registrarAutomaticoOmitido(
                        'La ejecucion programada no se inicio porque ya existia una indexacion en curso.',
                    );
                }

                Log::warning('No se pudo iniciar la indexacion automatica.', [
                    'type' => $exception->tipo,
                    'status' => $exception->statusHttp,
                    'message' => $exception->getMessage(),
                ]);
                $this->warn($exception->getMessage());
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Fallo inesperado al procesar las indexaciones del motor.', [
                'message' => $exception->getMessage(),
            ]);
            $this->error('No se pudieron procesar las indexaciones: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
