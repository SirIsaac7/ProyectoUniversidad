<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProveedorEspecialidad extends Pivot
{
    use LogsActivity;

    protected $table = 'proveedor_especialidad';

    public $incrementing = true;

    protected $fillable = [
        'perfil_proveedor_id',
        'especialidad_id',
        'es_principal',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'es_principal' => 'boolean',
            'estado' => 'boolean',
        ];
    }

    public function perfilProveedor()
    {
        return $this->belongsTo(PerfilProveedor::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('proveedor_especialidad')
            ->logOnly(['perfil_proveedor_id', 'especialidad_id', 'es_principal', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
