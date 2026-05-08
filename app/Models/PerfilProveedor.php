<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PerfilProveedor extends Model
{
    use LogsActivity;

    protected $table = 'perfiles_proveedores';

    protected $fillable = [
        'user_id',
        'nombre_publico',
        'descripcion',
        'foto_portada',
        'anios_experiencia',
        'estado_verificacion',
        'motivo_rechazo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'anios_experiencia' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'proveedor_especialidad')
            ->using(ProveedorEspecialidad::class)
            ->withPivot('es_principal', 'estado')
            ->withTimestamps();
    }

    public function proveedorEspecialidades()
    {
        return $this->hasMany(ProveedorEspecialidad::class);
    }

    public function horarios()
    {
        return $this->hasMany(HorarioProveedor::class);
    }

    public function ubicacion()
    {
        return $this->hasOne(UbicacionProveedor::class);
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoProveedor::class);
    }

    public function portafolio()
    {
        return $this->hasMany(PortafolioProveedor::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('perfiles_proveedores')
            ->logOnly([
                'user_id',
                'nombre_publico',
                'descripcion',
                'foto_portada',
                'anios_experiencia',
                'estado_verificacion',
                'motivo_rechazo',
                'estado',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
