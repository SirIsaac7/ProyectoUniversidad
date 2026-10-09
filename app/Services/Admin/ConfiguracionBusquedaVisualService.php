<?php

namespace App\Services\Admin;

use App\Models\ConfiguracionBusquedaVisual;
use Illuminate\Support\Arr;

class ConfiguracionBusquedaVisualService
{
    public const FASES = [
        'iniciando',
        'construyendo_filtros',
        'generando_embedding',
        'buscando_qdrant',
        'reranking',
        'normalizando_puntuaciones',
        'calculando_ranking_final',
        'preparando_resultados',
        'completado',
        'sin_resultados',
        'error',
    ];

    public function configuracion(): ConfiguracionBusquedaVisual
    {
        return ConfiguracionBusquedaVisual::firstOrCreate(['id' => 1], $this->valoresPredeterminados());
    }

    public function actualizar(array $datos): ConfiguracionBusquedaVisual
    {
        $permitidos = array_keys($this->valoresPredeterminados());
        $configuracion = $this->configuracion();
        $configuracion->update(Arr::only($datos, $permitidos));

        return $configuracion->refresh();
    }

    public function restaurar(): ConfiguracionBusquedaVisual
    {
        $configuracion = $this->configuracion();
        $configuracion->forceFill($this->valoresPredeterminados())->save();

        return $configuracion->refresh();
    }

    public function paraCliente(?ConfiguracionBusquedaVisual $configuracion = null): array
    {
        $configuracion ??= $this->configuracion();
        $predeterminados = $this->valoresPredeterminados();
        $mensajes = array_replace(
            $predeterminados['mensajes_fases'],
            is_array($configuracion->mensajes_fases) ? $configuracion->mensajes_fases : [],
        );

        $datos = [];
        foreach ($predeterminados as $campo => $valor) {
            $datos[$campo] = $campo === 'mensajes_fases'
                ? $mensajes
                : ($configuracion->{$campo} ?? $valor);
        }

        $datos['familia_css'] = $this->familiasTipograficas()[$datos['familia_tipografica']]
            ?? $this->familiasTipograficas()['system'];
        $datos['estilo'] = $this->estiloCss($datos);

        return $datos;
    }

    public function valoresPredeterminados(): array
    {
        return [
            'color_primario' => '#405189',
            'color_secundario' => '#6f5cff',
            'color_boton' => '#405189',
            'color_fondo' => '#f3f6f9',
            'color_indicador' => '#299cdb',
            'color_fase_completada' => '#0ab39c',
            'color_fase_actual' => '#f7b84b',
            'color_informativo' => '#299cdb',
            'color_error' => '#f06548',
            'color_destacado' => '#6f5cff',
            'familia_tipografica' => 'system',
            'tamano_titulos' => 22,
            'tamano_mensajes' => 14,
            'grosor_texto' => 600,
            'animacion' => 'spinner',
            'presentacion_fases' => 'actual',
            'diseno_tarjetas' => 'clasica',
            'radio_bordes' => 12,
            'espaciado' => 'normal',
            'distribucion' => 'estandar',
            'apariencia_calificacion' => 'estrellas',
            'apariencia_explicacion' => 'caja',
            'mensajes_fases' => [
                'iniciando' => 'Estamos preparando tu búsqueda...',
                'construyendo_filtros' => 'Interpretando el problema y tus preferencias...',
                'generando_embedding' => 'Comprendiendo semánticamente tu necesidad...',
                'buscando_qdrant' => 'Buscando técnicos relacionados con tu consulta...',
                'reranking' => 'Evaluando las coincidencias encontradas...',
                'normalizando_puntuaciones' => 'Comparando la relevancia de los proveedores...',
                'calculando_ranking_final' => 'Organizando los proveedores recomendados...',
                'preparando_resultados' => 'Preparando tus resultados...',
                'completado' => 'Búsqueda completada.',
                'sin_resultados' => 'No encontramos proveedores para esta consulta.',
                'error' => 'No pudimos completar la búsqueda.',
            ],
        ];
    }

    public function familiasTipograficas(): array
    {
        return [
            'system' => 'system-ui, -apple-system, "Segoe UI", sans-serif',
            'inter' => 'Inter, system-ui, sans-serif',
            'poppins' => 'Poppins, system-ui, sans-serif',
            'roboto' => 'Roboto, Arial, sans-serif',
            'arial' => 'Arial, Helvetica, sans-serif',
        ];
    }

    private function estiloCss(array $datos): string
    {
        $variables = [
            '--busqueda-primary' => $datos['color_primario'],
            '--busqueda-secondary' => $datos['color_secundario'],
            '--busqueda-button' => $datos['color_boton'],
            '--busqueda-background' => $datos['color_fondo'],
            '--busqueda-indicator' => $datos['color_indicador'],
            '--busqueda-phase-complete' => $datos['color_fase_completada'],
            '--busqueda-phase-current' => $datos['color_fase_actual'],
            '--busqueda-info' => $datos['color_informativo'],
            '--busqueda-error' => $datos['color_error'],
            '--busqueda-highlight' => $datos['color_destacado'],
            '--busqueda-font' => $datos['familia_css'],
            '--busqueda-title-size' => (int) $datos['tamano_titulos'].'px',
            '--busqueda-message-size' => (int) $datos['tamano_mensajes'].'px',
            '--busqueda-font-weight' => (int) $datos['grosor_texto'],
            '--busqueda-radius' => (int) $datos['radio_bordes'].'px',
        ];

        return collect($variables)
            ->map(fn ($valor, $nombre) => $nombre.':'.$valor)
            ->implode(';');
    }
}
