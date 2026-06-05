<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Solicitud extends Model
{
    use LogsActivity;

    protected $table = 'solicitudes';

    protected $fillable = [
        'cliente_user_id',
        'perfil_proveedor_id',
        'especialidad_id',
        'titulo',
        'descripcion',
        'tipo_atencion',
        'direccion',
        'zona',
        'latitud',
        'longitud',
        'fecha_solicitada',
        'hora_solicitada',
        'estado',
        'motivo_cancelacion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'fecha_solicitada' => 'date',
            'hora_solicitada' => 'datetime:H:i',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_user_id');
    }

    public function perfilProveedor()
    {
        return $this->belongsTo(PerfilProveedor::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function cita()
    {
        return $this->hasOne(Cita::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialSolicitud::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('solicitudes')
            ->logOnly([
                'cliente_user_id',
                'perfil_proveedor_id',
                'especialidad_id',
                'titulo',
                'descripcion',
                'tipo_atencion',
                'direccion',
                'zona',
                'latitud',
                'longitud',
                'fecha_solicitada',
                'hora_solicitada',
                'estado',
                'motivo_cancelacion',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
