<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EjecucionIndexacionMotor extends Model
{
    protected $table = 'ejecuciones_indexacion_motor';

    protected $fillable = [
        'fastapi_identificador',
        'tipo',
        'usuario_id',
        'estado',
        'categoria_estado',
        'fase_actual',
        'descripcion_fase',
        'iniciado_en',
        'finalizado_en',
        'total_proveedores',
        'proveedores_procesados',
        'proveedores_nuevos',
        'proveedores_reindexados',
        'metadatos_actualizados',
        'proveedores_sin_modificaciones',
        'proveedores_eliminados',
        'errores',
        'fases',
        'respuesta_inicio',
        'ultima_respuesta',
        'ultimo_error_seguimiento',
        'intentos_seguimiento',
        'ultimo_seguimiento_at',
    ];

    protected function casts(): array
    {
        return [
            'errores' => 'array',
            'fases' => 'array',
            'respuesta_inicio' => 'array',
            'ultima_respuesta' => 'array',
            'iniciado_en' => 'datetime',
            'finalizado_en' => 'datetime',
            'ultimo_seguimiento_at' => 'datetime',
            'intentos_seguimiento' => 'integer',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEnCurso(Builder $query): Builder
    {
        return $query->whereNotIn('categoria_estado', ['completado', 'error']);
    }

    public function getEsTerminalAttribute(): bool
    {
        return in_array($this->categoria_estado, ['completado', 'error'], true);
    }

    public function getDuracionSegundosAttribute(): ?int
    {
        if (! $this->iniciado_en) {
            return null;
        }

        return (int) $this->iniciado_en->diffInSeconds($this->finalizado_en ?? now());
    }
}
