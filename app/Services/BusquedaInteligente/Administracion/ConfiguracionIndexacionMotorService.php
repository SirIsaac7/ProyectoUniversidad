<?php

namespace App\Services\BusquedaInteligente\Administracion;

use App\Models\ConfiguracionIndexacionMotor;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class ConfiguracionIndexacionMotorService
{
    private const LOCK_PROGRAMACION = 'motor_busqueda.programacion';

    public function configuracion(): ConfiguracionIndexacionMotor
    {
        return ConfiguracionIndexacionMotor::firstOrCreate(
            ['id' => 1],
            [
                'activo' => false,
                'frecuencia' => 'diario',
                'hora_ejecucion' => '02:00:00',
                'zona_horaria' => 'America/La_Paz',
            ],
        );
    }

    public function actualizar(array $data): ConfiguracionIndexacionMotor
    {
        $configuracion = $this->configuracion();
        $configuracion->update([
            'activo' => (bool) ($data['activo'] ?? false),
            'frecuencia' => $data['frecuencia'],
            'hora_ejecucion' => $data['hora_ejecucion'].':00',
            'dia_semana' => $data['frecuencia'] === 'semanal' ? (int) $data['dia_semana'] : null,
            'zona_horaria' => 'America/La_Paz',
        ]);

        return $configuracion->refresh();
    }

    public function proximaEjecucion(?CarbonInterface $desde = null): ?CarbonImmutable
    {
        $configuracion = $this->configuracion();

        if (! $configuracion->activo) {
            return null;
        }

        $zonaHoraria = $configuracion->zona_horaria ?: 'America/La_Paz';
        $ahora = $desde
            ? CarbonImmutable::instance($desde)->setTimezone($zonaHoraria)
            : CarbonImmutable::now($zonaHoraria);
        [$hora, $minuto] = array_map('intval', explode(':', substr($configuracion->hora_ejecucion, 0, 5)));

        if ($configuracion->frecuencia === 'semanal') {
            $diasHastaEjecucion = ((int) $configuracion->dia_semana - $ahora->dayOfWeekIso + 7) % 7;
            $proxima = $ahora->startOfDay()->addDays($diasHastaEjecucion)->setTime($hora, $minuto);

            return $proxima->lessThanOrEqualTo($ahora) ? $proxima->addWeek() : $proxima;
        }

        $proxima = $ahora->startOfDay()->setTime($hora, $minuto);

        return $proxima->lessThanOrEqualTo($ahora) ? $proxima->addDay() : $proxima;
    }

    public function reclamarEjecucionProgramada(?CarbonInterface $momento = null): bool
    {
        return Cache::lock(self::LOCK_PROGRAMACION, 10)->block(3, function () use ($momento) {
            $configuracion = $this->configuracion()->refresh();

            if (! $configuracion->activo) {
                return false;
            }

            $zonaHoraria = $configuracion->zona_horaria ?: 'America/La_Paz';
            $ahora = $momento
                ? CarbonImmutable::instance($momento)->setTimezone($zonaHoraria)
                : CarbonImmutable::now($zonaHoraria);
            $horaProgramada = substr($configuracion->hora_ejecucion, 0, 5);

            if ($ahora->format('H:i') !== $horaProgramada) {
                return false;
            }

            if ($configuracion->frecuencia === 'semanal' && (int) $configuracion->dia_semana !== $ahora->dayOfWeekIso) {
                return false;
            }

            $minutoActual = $ahora->startOfMinute();
            $ultima = $configuracion->ultima_ejecucion_programada_at?->setTimezone($zonaHoraria)->startOfMinute();

            if ($ultima?->equalTo($minutoActual)) {
                return false;
            }

            $configuracion->update(['ultima_ejecucion_programada_at' => $minutoActual]);

            return true;
        });
    }
}
