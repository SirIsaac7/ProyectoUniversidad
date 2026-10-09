<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EjecucionBusquedaMotor extends Model
{
    protected $table = 'ejecuciones_busqueda_motor';

    protected $fillable = [
        'fastapi_identificador',
        'usuario_id',
        'consulta',
        'filtros',
        'estado',
        'categoria_estado',
        'fase_actual',
        'detalle_fase',
        'historial_fases',
        'error',
        'respuesta_inicio',
        'ultima_respuesta',
        'iniciado_en',
        'finalizado_en',
        'resultados_recuperados_at',
    ];

    protected function casts(): array
    {
        return [
            'filtros' => 'array',
            'historial_fases' => 'array',
            'respuesta_inicio' => 'array',
            'ultima_respuesta' => 'array',
            'iniciado_en' => 'datetime',
            'finalizado_en' => 'datetime',
            'resultados_recuperados_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function getEsTerminalAttribute(): bool
    {
        return in_array($this->categoria_estado, ['completado', 'sin_resultados', 'error'], true);
    }
}
