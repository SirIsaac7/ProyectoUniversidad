<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionIndexacionMotor extends Model
{
    protected $table = 'configuraciones_indexacion_motor';

    protected $fillable = [
        'activo',
        'frecuencia',
        'hora_ejecucion',
        'dia_semana',
        'zona_horaria',
        'ultima_ejecucion_programada_at',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'dia_semana' => 'integer',
            'ultima_ejecucion_programada_at' => 'datetime',
        ];
    }
}
