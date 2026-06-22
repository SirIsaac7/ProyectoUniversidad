<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    public function enviarMensaje(string $numero, string $mensaje): bool
    {
        if (! config('services.evolution_api.enabled')) {
            return false;
        }

        $numero = $this->normalizarNumero($numero);

        if (! $numero || trim($mensaje) === '') {
            return false;
        }

        try {
            $response = Http::timeout((int) config('services.evolution_api.timeout', 15))
                ->withHeaders([
                    'apikey' => config('services.evolution_api.key'),
                ])
                ->post(
                    rtrim(config('services.evolution_api.url'), '/') .
                    '/message/sendText/' .
                    config('services.evolution_api.instance'),
                    [
                        'number' => $numero,
                        'text' => $mensaje,
                    ]
                );

            if (! $response->successful()) {
                Log::error('Evolution API no pudo enviar el mensaje.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Error conectando con Evolution API.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function normalizarNumero(string $numero): ?string
    {
        $numero = preg_replace('/\D+/', '', $numero);

        if (! $numero) {
            return null;
        }

        if (strlen($numero) === 8) {
            return '591' . $numero;
        }

        return $numero;
    }
}
