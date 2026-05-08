<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PortafolioProveedor extends Model
{
    use LogsActivity;

    protected $table = 'portafolio_proveedor';

    protected $fillable = [
        'perfil_proveedor_id',
        'titulo',
        'descripcion',
        'imagen',
        'fecha_trabajo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_trabajo' => 'date',
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
            ->useLogName('portafolio_proveedor')
            ->logOnly(['perfil_proveedor_id', 'titulo', 'descripcion', 'imagen', 'fecha_trabajo', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
