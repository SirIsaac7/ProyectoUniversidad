<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cita extends Model
{
    use LogsActivity;

    protected $table = 'citas';

    protected $fillable = [
        'solicitud_id',
        'fecha_cita',
        'hora_inicio',
        'hora_fin',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cita' => 'date',
            'hora_inicio' => 'datetime:H:i',
            'hora_fin' => 'datetime:H:i',
        ];
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('citas')
            ->logOnly([
                'solicitud_id',
                'fecha_cita',
                'hora_inicio',
                'hora_fin',
                'estado',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
