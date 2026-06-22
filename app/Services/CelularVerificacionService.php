<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CelularVerificacionService
{
    public function __construct(
        protected EvolutionApiService $evolutionApiService
    ) {
    }

    public function enviarCodigo(User $usuario): bool
    {
        if (blank($usuario->celular)) {
            return false;
        }

        $codigo = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($usuario), $codigo, now()->addMinutes(10));

        return $this->evolutionApiService->enviarMensaje(
            $usuario->celular,
            'Tu codigo de verificacion de TecnoConexion es: ' . $codigo . '. Este codigo vence en 10 minutos.'
        );
    }

    public function verificar(User $usuario, string $codigo): bool
    {
        $codigoGuardado = Cache::get($this->cacheKey($usuario));

        if (! $codigoGuardado || ! hash_equals((string) $codigoGuardado, $codigo)) {
            return false;
        }

        $usuario->forceFill([
            'celular_verificado_at' => now(),
        ])->save();

        Cache::forget($this->cacheKey($usuario));

        return true;
    }

    protected function cacheKey(User $usuario): string
    {
        return 'verificacion_celular_usuario_' . $usuario->id;
    }
}
