<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HorarioProveedor extends Model
{
    use LogsActivity;

    protected $table = 'horarios_proveedor';

    protected $fillable = [
        'perfil_proveedor_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'tipo_atencion',
        'disponible',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
            'hora_inicio' => 'datetime:H:i',
            'hora_fin' => 'datetime:H:i',
            'disponible' => 'boolean',
            'estado' => 'boolean',
        ];
    }

    public function perfilProveedor()
    {
        return $this->belongsTo(PerfilProveedor::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('horarios_proveedor')
            ->logOnly(['perfil_proveedor_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'tipo_atencion', 'disponible', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
