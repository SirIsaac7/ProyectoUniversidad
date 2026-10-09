<?php

namespace App\Services\Admin;

use App\Exceptions\MotorBusquedaException;
use App\Models\EjecucionIndexacionMotor;
use App\Models\User;
use App\Services\Cliente\MotorBusquedaService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MotorIndexacionService
{
    private const LOCK_INICIAR = 'motor_busqueda.iniciar_indexacion';

    public function __construct(
        protected MotorBusquedaService $motorBusquedaService
    ) {}

    public function iniciarManual(User $usuario): EjecucionIndexacionMotor
    {
        return $this->iniciar('manual', $usuario);
    }

    public function iniciarAutomatica(): EjecucionIndexacionMotor
    {
        return $this->iniciar('automatica');
    }

    public function iniciar(string $tipo, ?User $usuario = null): EjecucionIndexacionMotor
    {
        try {
            return Cache::lock(self::LOCK_INICIAR, 90)->block(5, function () use ($tipo, $usuario) {
                if (EjecucionIndexacionMotor::query()->enCurso()->exists()) {
                    throw new MotorBusquedaException(
                        'Ya existe una indexacion en curso.',
                        'conflicto',
                        409,
                    );
                }

                $ejecucion = EjecucionIndexacionMotor::create([
                    'tipo' => $tipo,
                    'usuario_id' => $tipo === 'manual' ? $usuario?->id : null,
                    'estado' => 'solicitando',
                    'categoria_estado' => 'pendiente',
                    'iniciado_en' => now(),
                ]);

                try {
                    $respuesta = $this->motorBusquedaService->iniciarIndexacion();
                    $identificador = $this->extraerIdentificador($respuesta);

                    if ($identificador === null) {
                        throw new MotorBusquedaException(
                            'El motor acepto la indexacion, pero no devolvio su identificador.',
                            'json_invalido',
                        );
                    }

                    $estado = (string) ($respuesta['estado'] ?? 'pendiente');
                    $ejecucion->update([
                        'fastapi_identificador' => $identificador,
                        'estado' => $estado,
                        'categoria_estado' => $this->categoriaEjecucion($estado),
                        'respuesta_inicio' => $respuesta,
                        'ultima_respuesta' => $respuesta,
                    ]);

                    return $ejecucion->refresh();
                } catch (Throwable $exception) {
                    $this->marcarError($ejecucion, $exception->getMessage());

                    if ($exception instanceof MotorBusquedaException) {
                        throw $exception;
                    }

                    throw new MotorBusquedaException(
                        'No se pudo registrar el inicio de la indexacion.',
                        'inesperado',
                        previous: $exception,
                    );
                }
            });
        } catch (LockTimeoutException $exception) {
            throw new MotorBusquedaException(
                'Otra solicitud de indexacion se esta procesando. Intenta nuevamente.',
                'conflicto',
                409,
                $exception,
            );
        }
    }

    public function registrarAutomaticoOmitido(string $mensaje): EjecucionIndexacionMotor
    {
        return EjecucionIndexacionMotor::create([
            'tipo' => 'automatica',
            'usuario_id' => null,
            'estado' => 'error',
            'categoria_estado' => 'error',
            'iniciado_en' => now(),
            'finalizado_en' => now(),
            'errores' => [$mensaje],
        ]);
    }

    public function actualizarEnCurso(): array
    {
        $resultado = ['consultadas' => 0, 'actualizadas' => 0, 'fallidas' => 0];

        EjecucionIndexacionMotor::query()
            ->enCurso()
            ->whereNull('fastapi_identificador')
            ->where('created_at', '<=', now()->subMinutes(5))
            ->get()
            ->each(fn (EjecucionIndexacionMotor $ejecucion) => $this->marcarError(
                $ejecucion,
                'Laravel se reinicio antes de conservar el identificador devuelto por FastAPI.',
            ));

        EjecucionIndexacionMotor::query()
            ->enCurso()
            ->whereNotNull('fastapi_identificador')
            ->orderBy('id')
            ->each(function (EjecucionIndexacionMotor $ejecucion) use (&$resultado) {
                $resultado['consultadas']++;

                try {
                    $this->actualizar($ejecucion);
                    $resultado['actualizadas']++;
                } catch (Throwable $exception) {
                    $resultado['fallidas']++;
                    Log::warning('No se pudo actualizar una indexacion del motor.', [
                        'ejecucion_id' => $ejecucion->id,
                        'fastapi_identificador' => $ejecucion->fastapi_identificador,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });

        return $resultado;
    }

    public function actualizar(EjecucionIndexacionMotor $ejecucion): EjecucionIndexacionMotor
    {
        if ($ejecucion->es_terminal || ! $ejecucion->fastapi_identificador) {
            return $ejecucion;
        }

        try {
            return Cache::lock('motor_busqueda.seguimiento.'.$ejecucion->id, 90)->block(3, function () use ($ejecucion) {
                $ejecucion->refresh();

                if ($ejecucion->es_terminal) {
                    return $ejecucion;
                }

                try {
                    $respuesta = $this->motorBusquedaService->consultarIndexacion($ejecucion->fastapi_identificador);
                    $normalizada = $this->normalizarRespuesta($respuesta, $ejecucion->fastapi_identificador);
                    $resultados = $normalizada['resultados'];
                    $ejecucion->update([
                        'estado' => $normalizada['estado'],
                        'categoria_estado' => $normalizada['categoria_estado'],
                        'fase_actual' => $normalizada['fase_actual'],
                        'descripcion_fase' => $normalizada['descripcion_fase'],
                        'iniciado_en' => $normalizada['iniciado_en'] ?: $ejecucion->iniciado_en,
                        'finalizado_en' => $normalizada['finalizado_en'] ?: ($normalizada['terminal'] ? now() : null),
                        'total_proveedores' => $normalizada['total_proveedores'],
                        'proveedores_procesados' => $normalizada['proveedores_procesados'],
                        'proveedores_nuevos' => $resultados['proveedores_nuevos'],
                        'proveedores_reindexados' => $resultados['proveedores_reindexados'],
                        'metadatos_actualizados' => $resultados['metadatos_actualizados'],
                        'proveedores_sin_modificaciones' => $resultados['proveedores_sin_modificaciones'],
                        'proveedores_eliminados' => $resultados['proveedores_eliminados'],
                        'errores' => $normalizada['errores'],
                        'fases' => $normalizada['fases'],
                        'ultima_respuesta' => $respuesta,
                        'ultimo_error_seguimiento' => null,
                        'intentos_seguimiento' => $ejecucion->intentos_seguimiento + 1,
                        'ultimo_seguimiento_at' => now(),
                    ]);

                    return $ejecucion->refresh();
                } catch (MotorBusquedaException $exception) {
                    if ($exception->statusHttp === 404) {
                        $this->marcarError(
                            $ejecucion,
                            'La ejecucion ya no existe en FastAPI. Es posible que el motor se haya reiniciado.',
                        );

                        return $ejecucion->refresh();
                    }

                    $ejecucion->update([
                        'ultimo_error_seguimiento' => $exception->getMessage(),
                        'intentos_seguimiento' => $ejecucion->intentos_seguimiento + 1,
                        'ultimo_seguimiento_at' => now(),
                    ]);

                    throw $exception;
                }
            });
        } catch (LockTimeoutException) {
            return $ejecucion->refresh();
        }
    }

    public function actualizarPorIdentificador(string $identificador): EjecucionIndexacionMotor
    {
        $ejecucion = EjecucionIndexacionMotor::where('fastapi_identificador', $identificador)->first();

        if (! $ejecucion) {
            throw new MotorBusquedaException('La ejecucion solicitada no existe en el historial.', 'respuesta_http', 404);
        }

        return $this->actualizar($ejecucion);
    }

    public function ejecucionActiva(): ?EjecucionIndexacionMotor
    {
        return EjecucionIndexacionMotor::query()->enCurso()->latest('id')->first();
    }

    public function historial(array $filtros = [], int $porPagina = 10): LengthAwarePaginator
    {
        return EjecucionIndexacionMotor::query()
            ->with('usuario:id,name')
            ->when($filtros['fecha_desde'] ?? null, fn ($query, $fecha) => $query->whereDate('created_at', '>=', $fecha))
            ->when($filtros['fecha_hasta'] ?? null, fn ($query, $fecha) => $query->whereDate('created_at', '<=', $fecha))
            ->when($filtros['tipo'] ?? null, fn ($query, $tipo) => $query->where('tipo', $tipo))
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('categoria_estado', $estado))
            ->latest('id')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public function normalizarRespuesta(array $respuesta, string $identificadorSolicitado): array
    {
        $datos = $this->contenedorIndexacion($respuesta);
        $estado = (string) ($datos['estado'] ?? 'desconocido');
        $categoria = $this->categoriaEjecucion($estado);
        $total = $this->enteroONull($datos['total_proveedores'] ?? $datos['total'] ?? null);
        $procesados = $this->enteroONull($datos['proveedores_procesados'] ?? $datos['procesados'] ?? null);
        $resultados = is_array($datos['resultados'] ?? null) ? $datos['resultados'] : [];
        $fases = is_array($datos['fases'] ?? null)
            ? $datos['fases']
            : (is_array($datos['historial_fases'] ?? null) ? $datos['historial_fases'] : []);
        $errores = $datos['errores'] ?? ($datos['error'] ?? []);

        return [
            'identificador' => $this->extraerIdentificador($datos) ?? trim($identificadorSolicitado),
            'estado' => $estado,
            'categoria_estado' => $categoria,
            'terminal' => in_array($categoria, ['completado', 'error'], true),
            'fase_actual' => $datos['fase_actual'] ?? null,
            'descripcion_fase' => $datos['descripcion_fase'] ?? null,
            'iniciado_en' => $datos['iniciado_en'] ?? $datos['fecha_inicio'] ?? null,
            'finalizado_en' => $datos['finalizado_en'] ?? $datos['fecha_fin'] ?? null,
            'total_proveedores' => $total,
            'proveedores_procesados' => $procesados,
            'porcentaje' => $total !== null && $total > 0 && $procesados !== null
                ? min(100, round(($procesados / $total) * 100, 2))
                : null,
            'errores' => is_array($errores) ? array_values($errores) : (filled($errores) ? [(string) $errores] : []),
            'resultados' => [
                'total_proveedores' => $this->enteroONull($resultados['total_proveedores'] ?? $total),
                'proveedores_nuevos' => $this->enteroONull($resultados['proveedores_nuevos'] ?? null),
                'proveedores_reindexados' => $this->enteroONull($resultados['proveedores_reindexados'] ?? null),
                'metadatos_actualizados' => $this->enteroONull($resultados['metadatos_actualizados'] ?? $resultados['proveedores_metadatos_actualizados'] ?? null),
                'proveedores_sin_modificaciones' => $this->enteroONull($resultados['proveedores_sin_modificaciones'] ?? $resultados['proveedores_sin_cambios'] ?? null),
                'proveedores_eliminados' => $this->enteroONull($resultados['proveedores_eliminados'] ?? $resultados['proveedores_eliminados_indice'] ?? null),
            ],
            'fases' => array_values($fases),
            'respuesta' => $respuesta,
        ];
    }

    public function paraPanel(EjecucionIndexacionMotor $ejecucion): array
    {
        $total = $ejecucion->total_proveedores;
        $procesados = $ejecucion->proveedores_procesados;

        return [
            'id' => $ejecucion->id,
            'identificador' => $ejecucion->fastapi_identificador,
            'estado' => $ejecucion->estado,
            'categoria_estado' => $ejecucion->categoria_estado,
            'terminal' => $ejecucion->es_terminal,
            'fase_actual' => $ejecucion->fase_actual,
            'descripcion_fase' => $ejecucion->descripcion_fase,
            'iniciado_en' => $ejecucion->iniciado_en?->toIso8601String(),
            'finalizado_en' => $ejecucion->finalizado_en?->toIso8601String(),
            'total_proveedores' => $total,
            'proveedores_procesados' => $procesados,
            'porcentaje' => $total && $procesados !== null ? min(100, round(($procesados / $total) * 100, 2)) : null,
            'errores' => $ejecucion->errores ?? [],
            'resultados' => [
                'total_proveedores' => $total,
                'proveedores_nuevos' => $ejecucion->proveedores_nuevos,
                'proveedores_reindexados' => $ejecucion->proveedores_reindexados,
                'metadatos_actualizados' => $ejecucion->metadatos_actualizados,
                'proveedores_sin_modificaciones' => $ejecucion->proveedores_sin_modificaciones,
                'proveedores_eliminados' => $ejecucion->proveedores_eliminados,
            ],
            'fases' => $ejecucion->fases ?? [],
            'ultimo_error_seguimiento' => $ejecucion->ultimo_error_seguimiento,
        ];
    }

    private function marcarError(EjecucionIndexacionMotor $ejecucion, string $mensaje): void
    {
        $errores = collect($ejecucion->errores ?? [])->push($mensaje)->filter()->unique()->values()->all();
        $ejecucion->update([
            'estado' => 'error',
            'categoria_estado' => 'error',
            'finalizado_en' => now(),
            'errores' => $errores,
            'ultimo_error_seguimiento' => $mensaje,
            'ultimo_seguimiento_at' => now(),
        ]);
    }

    private function contenedorIndexacion(array $respuesta): array
    {
        foreach (['indexacion', 'ejecucion', 'datos'] as $clave) {
            if (is_array($respuesta[$clave] ?? null)) {
                return $respuesta[$clave];
            }
        }

        return $respuesta;
    }

    private function extraerIdentificador(array $respuesta): ?string
    {
        foreach (['identificador', 'ejecucion_id', 'id'] as $clave) {
            if (is_string($respuesta[$clave] ?? null) && trim($respuesta[$clave]) !== '') {
                return trim($respuesta[$clave]);
            }
        }

        foreach (['indexacion', 'ejecucion', 'datos'] as $clave) {
            if (is_array($respuesta[$clave] ?? null)) {
                $identificador = $this->extraerIdentificador($respuesta[$clave]);

                if ($identificador !== null) {
                    return $identificador;
                }
            }
        }

        return null;
    }

    private function categoriaEjecucion(string $estado): string
    {
        $normalizado = Str::of($estado)->ascii()->lower()->replace([' ', '-'], '_')->toString();

        return match (true) {
            in_array($normalizado, ['completado', 'completada', 'completed', 'finalizado', 'finalizada', 'finished', 'success'], true) => 'completado',
            in_array($normalizado, ['error', 'failed', 'fallido', 'fallida', 'cancelado', 'cancelada', 'cancelled'], true) => 'error',
            in_array($normalizado, ['pendiente', 'pending', 'queued', 'en_cola', 'solicitando'], true) => 'pendiente',
            in_array($normalizado, ['en_ejecucion', 'ejecutando', 'running', 'processing', 'in_progress'], true) => 'ejecucion',
            default => 'desconocido',
        };
    }

    private function enteroONull(mixed $valor): ?int
    {
        return is_numeric($valor) ? (int) $valor : null;
    }
}
