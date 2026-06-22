<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RespuestaCalificacion extends Model
{
    use LogsActivity;

    protected $table = 'respuestas_calificacion';

    protected $fillable = [
        'calificacion_id',
        'user_id',
        'respuesta',
        'estado',
    ];

    public function fueEditada(): bool
    {
        if ($this->activities()->where('event', 'updated')->exists()) {
            return true;
        }

        if (! $this->created_at || ! $this->updated_at) {
            return false;
        }

        return ! $this->created_at->equalTo($this->updated_at);
    }

    public function calificacion()
    {
        return $this->belongsTo(Calificacion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('respuestas_calificacion')
            ->logOnly([
                'calificacion_id',
                'user_id',
                'respuesta',
                'estado',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
