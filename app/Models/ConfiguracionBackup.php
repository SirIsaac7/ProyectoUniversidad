<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionBackup extends Model
{
    protected $table = 'configuracion_backups';

    protected $fillable = [
        'hora_ejecucion',
        'frecuencia',
        'dia_semana',
        'dia_mes',
        'activo',
        'ultimo_backup_at',
        'ultimo_estado',
        'ultimo_mensaje',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'ultimo_backup_at' => 'datetime',
        ];
    }
}
