<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AspectoCalificacion extends Model
{
    use LogsActivity;

    protected $table = 'aspectos_calificacion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCalificacion::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('aspectos_calificacion')
            ->logOnly([
                'nombre',
                'descripcion',
                'estado',
                'orden',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
