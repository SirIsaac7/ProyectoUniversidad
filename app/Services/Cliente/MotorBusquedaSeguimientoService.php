<?php

namespace App\Services\Cliente;

use App\Exceptions\MotorBusquedaException;
use App\Models\EjecucionBusquedaMotor;
use App\Models\User;
use Illuminate\Support\Str;

class MotorBusquedaSeguimientoService
{
    public function __construct(
        protected MotorBusquedaService $motorBusquedaService,
        protected BusquedaServicioService $busquedaServicioService,
    ) {}

    public function iniciar(User $usuario, string $consulta, array $filtros): EjecucionBusquedaMotor
    {
        $consulta = trim($consulta);
        $filtros = $this->normalizarFiltros($filtros);
        $respuesta = $this->motorBusquedaService->iniciarBusqueda($consulta, $filtros);
        $identificador = $this->extraerIdentificador($respuesta);

        if ($identificador === null) {
            throw new MotorBusquedaException(
                'El motor aceptó la búsqueda, pero no devolvió un identificador válido.',
                'json_invalido',
            );
        }

        return EjecucionBusquedaMotor::create([
            'fastapi_identificador' => $identificador,
            'usuario_id' => $usuario->id,
            'consulta' => $consulta,
            'filtros' => $filtros,
            'estado' => (string) ($respuesta['estado'] ?? 'pendiente'),
            'categoria_estado' => 'pendiente',
            'fase_actual' => 'iniciando',
            'respuesta_inicio' => $respuesta,
            'iniciado_en' => now(),
        ]);
    }

    public function actualizar(EjecucionBusquedaMotor $ejecucion): EjecucionBusquedaMotor
    {
        if ($ejecucion->es_terminal) {
            return $ejecucion;
        }

        try {
            $respuesta = $this->motorBusquedaService->consultarBusqueda($ejecucion->fastapi_identificador);
        } catch (MotorBusquedaException $exception) {
            if ($exception->statusHttp === 404) {
                $ejecucion->update([
                    'estado' => 'error',
                    'categoria_estado' => 'error',
                    'fase_actual' => 'error',
                    'detalle_fase' => 'La búsqueda dejó de estar disponible en el motor.',
                    'error' => 'La ejecución ya no existe en FastAPI. Es posible que el motor se haya reiniciado.',
                    'finalizado_en' => now(),
                ]);

                return $ejecucion->refresh();
            }

            throw $exception;
        }

        $estado = (string) ($respuesta['estado'] ?? 'desconocido');
        $fase = is_string($respuesta['fase'] ?? null) ? $respuesta['fase'] : null;
        $categoria = $this->categoria($estado, $fase);
        $error = $respuesta['error'] ?? null;
        $terminal = in_array($categoria, ['completado', 'sin_resultados', 'error'], true);

        $ejecucion->update([
            'estado' => $estado,
            'categoria_estado' => $categoria,
            'fase_actual' => $fase,
            'detalle_fase' => is_string($respuesta['detalle'] ?? null) ? $respuesta['detalle'] : null,
            'historial_fases' => $this->normalizarHistorial($respuesta['historial_fases'] ?? []),
            'error' => is_string($error) && filled($error) ? Str::limit($error, 1000) : null,
            'ultima_respuesta' => $respuesta,
            'iniciado_en' => $respuesta['creado_en'] ?? $ejecucion->iniciado_en,
            'finalizado_en' => $respuesta['finalizado_en'] ?? ($terminal ? now() : null),
        ]);

        return $ejecucion->refresh();
    }

    public function resultados(EjecucionBusquedaMotor $ejecucion): array
    {
        $ejecucion = $this->actualizar($ejecucion);

        if ($ejecucion->categoria_estado === 'error') {
            throw new MotorBusquedaException(
                $ejecucion->error ?: 'La búsqueda finalizó con un error.',
                'respuesta_http',
                422,
            );
        }

        if (! in_array($ejecucion->categoria_estado, ['completado', 'sin_resultados'], true)) {
            throw new MotorBusquedaException('La búsqueda todavía se encuentra en ejecución.', 'conflicto', 409);
        }

        $respuesta = $this->motorBusquedaService->resultadosBusqueda($ejecucion->fastapi_identificador);

        if (! isset($respuesta['resultados']) || ! is_array($respuesta['resultados'])) {
            throw new MotorBusquedaException(
                'El motor devolvió resultados que no se pudieron interpretar.',
                'json_invalido',
            );
        }

        $proveedores = $this->busquedaServicioService->proveedoresSemanticos($respuesta['resultados']);
        $ejecucion->update(['resultados_recuperados_at' => now()]);

        return [
            'consulta' => is_string($respuesta['consulta'] ?? null) ? $respuesta['consulta'] : $ejecucion->consulta,
            'total_motor' => is_numeric($respuesta['total'] ?? null)
                ? (int) $respuesta['total']
                : count($respuesta['resultados']),
            'total' => count($proveedores),
            'proveedores' => $proveedores,
        ];
    }

    public function paraCliente(EjecucionBusquedaMotor $ejecucion): array
    {
        return [
            'id' => $ejecucion->id,
            'estado' => $ejecucion->estado,
            'categoria_estado' => $ejecucion->categoria_estado,
            'fase' => $ejecucion->fase_actual,
            'detalle' => $ejecucion->detalle_fase,
            'historial_fases' => $ejecucion->historial_fases ?? [],
            'terminal' => $ejecucion->es_terminal,
            'error' => $ejecucion->error,
        ];
    }

    private function normalizarFiltros(array $filtros): array
    {
        return [
            'disponible' => ($filtros['disponible'] ?? null) === true ? true : null,
            'tipo_atencion' => in_array($filtros['tipo_atencion'] ?? null, ['domicilio', 'local'], true)
                ? $filtros['tipo_atencion']
                : null,
        ];
    }

    private function extraerIdentificador(array $respuesta): ?string
    {
        foreach (['id', 'identificador', 'ejecucion_id'] as $clave) {
            if (is_string($respuesta[$clave] ?? null) && trim($respuesta[$clave]) !== '') {
                return trim($respuesta[$clave]);
            }
        }

        return null;
    }

    private function categoria(string $estado, ?string $fase): string
    {
        $estado = Str::of($estado)->ascii()->lower()->replace([' ', '-'], '_')->toString();
        $fase = Str::of($fase ?? '')->ascii()->lower()->replace([' ', '-'], '_')->toString();

        if ($fase === 'sin_resultados') {
            return 'sin_resultados';
        }

        return match (true) {
            in_array($estado, ['completado', 'completada', 'completed', 'finished'], true) => 'completado',
            in_array($estado, ['error', 'failed', 'fallido', 'fallida', 'cancelado', 'cancelada'], true) => 'error',
            in_array($estado, ['ejecutando', 'en_ejecucion', 'running', 'processing', 'in_progress'], true) => 'ejecucion',
            default => 'pendiente',
        };
    }

    private function normalizarHistorial(mixed $historial): array
    {
        if (! is_array($historial)) {
            return [];
        }

        return collect($historial)
            ->filter(fn ($fase) => is_array($fase))
            ->take(30)
            ->map(fn (array $fase) => [
                'fase' => is_string($fase['fase'] ?? null) ? Str::limit($fase['fase'], 80) : null,
                'fecha' => is_string($fase['fecha'] ?? null) ? Str::limit($fase['fecha'], 80) : null,
                'detalle' => is_string($fase['detalle'] ?? null) ? Str::limit($fase['detalle'], 300) : null,
            ])
            ->values()
            ->all();
    }
}
