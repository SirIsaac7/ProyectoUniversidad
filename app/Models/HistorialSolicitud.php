<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HistorialSolicitud extends Model
{
    use LogsActivity;

    protected $table = 'historial_solicitudes';

    protected $fillable = [
        'solicitud_id',
        'user_id',
        'estado_anterior',
        'estado_nuevo',
        'comentario',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('historial_solicitudes')
            ->logOnly([
                'solicitud_id',
                'user_id',
                'estado_anterior',
                'estado_nuevo',
                'comentario',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
