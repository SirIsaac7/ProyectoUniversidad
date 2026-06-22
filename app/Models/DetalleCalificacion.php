<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DetalleCalificacion extends Model
{
    use LogsActivity;

    protected $table = 'detalle_calificaciones';

    protected $fillable = [
        'calificacion_id',
        'aspecto_calificacion_id',
        'puntuacion',
    ];

    protected function casts(): array
    {
        return [
            'puntuacion' => 'integer',
        ];
    }

    public function calificacion()
    {
        return $this->belongsTo(Calificacion::class);
    }

    public function aspecto()
    {
        return $this->belongsTo(AspectoCalificacion::class, 'aspecto_calificacion_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('detalle_calificaciones')
            ->logOnly([
                'calificacion_id',
                'aspecto_calificacion_id',
                'puntuacion',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
