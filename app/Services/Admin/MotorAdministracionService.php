<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\Cliente\MotorBusquedaService;
use Illuminate\Support\Str;

class MotorAdministracionService
{
    public function __construct(
        protected MotorBusquedaService $motorBusquedaService,
        protected MotorIndexacionService $motorIndexacionService,
    ) {}

    public function estadoServicios(): array
    {
        $respuesta = $this->motorBusquedaService->estado();
        $contenedor = is_array($respuesta['servicios'] ?? null) ? $respuesta['servicios'] : $respuesta;
        $servicios = collect([
            'fastapi' => 'FastAPI',
            'mysql' => 'MySQL',
            'qdrant' => 'Qdrant',
        ])->mapWithKeys(function (string $nombre, string $clave) use ($contenedor) {
            $valor = $this->buscarValorServicio($contenedor, $clave);

            return [$clave => $this->normalizarServicio($nombre, $valor)];
        })->all();

        $categorias = collect($servicios)->pluck('categoria');
        $general = $categorias->contains('desconectado')
            ? 'degradado'
            : ($categorias->every(fn ($categoria) => $categoria === 'activo') ? 'operativo' : 'indeterminado');

        return [
            'estado_general' => $general,
            'servicios' => $servicios,
            'consultado_en' => now()->toIso8601String(),
            'respuesta' => $respuesta,
        ];
    }

    public function iniciarIndexacion(User $usuario): array
    {
        $ejecucion = $this->motorIndexacionService->iniciarManual($usuario);

        return [
            'ejecucion_id' => $ejecucion->id,
            'identificador' => $ejecucion->fastapi_identificador,
            'mensaje' => $ejecucion->respuesta_inicio['mensaje'] ?? 'La indexacion fue aceptada por el motor.',
        ];
    }

    public function consultarIndexacion(string $identificador): array
    {
        $ejecucion = $this->motorIndexacionService->actualizarPorIdentificador($identificador);

        return $this->motorIndexacionService->paraPanel($ejecucion);
    }

    public function identificadorActivo(): ?string
    {
        return $this->motorIndexacionService->ejecucionActiva()?->fastapi_identificador;
    }

    private function buscarValorServicio(array $contenedor, string $clave): mixed
    {
        foreach ($contenedor as $nombre => $valor) {
            if (Str::lower((string) $nombre) === $clave) {
                return $valor;
            }
        }

        return null;
    }

    private function normalizarServicio(string $nombre, mixed $valor): array
    {
        $estado = match (true) {
            is_bool($valor) => $valor ? 'activo' : 'desconectado',
            is_string($valor) => $valor,
            is_array($valor) => (string) ($valor['estado'] ?? $valor['status'] ?? ($valor['ok'] ?? 'desconocido')),
            default => 'desconocido',
        };
        $normalizado = Str::lower(Str::ascii($estado));
        $categoria = match (true) {
            in_array($normalizado, ['ok', 'activo', 'active', 'conectado', 'connected', 'disponible', 'healthy', 'true', '1'], true) => 'activo',
            in_array($normalizado, ['error', 'inactivo', 'inactive', 'desconectado', 'disconnected', 'no disponible', 'unavailable', 'false', '0'], true) => 'desconectado',
            default => 'desconocido',
        };

        return [
            'nombre' => $nombre,
            'estado' => $estado,
            'categoria' => $categoria,
            'detalle' => is_array($valor) ? ($valor['detalle'] ?? $valor['mensaje'] ?? null) : null,
        ];
    }
}
