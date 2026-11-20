<?php

namespace App\Services\BusquedaInteligente\Administracion;

use App\Services\BusquedaInteligente\MotorBusquedaService;
use Illuminate\Support\Str;

class EstadoMotorService
{
    public function __construct(
        protected MotorBusquedaService $motorBusquedaService,
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
