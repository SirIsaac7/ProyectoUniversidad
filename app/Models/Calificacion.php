<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Calificacion extends Model
{
    use LogsActivity;

    protected $table = 'calificaciones';

    protected $fillable = [
        'cita_id',
        'puntuacion',
        'comentario',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'puntuacion' => 'integer',
        ];
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCalificacion::class);
    }

    public function respuesta()
    {
        return $this->hasOne(RespuestaCalificacion::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('calificaciones')
            ->logOnly([
                'cita_id',
                'puntuacion',
                'comentario',
                'estado',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
