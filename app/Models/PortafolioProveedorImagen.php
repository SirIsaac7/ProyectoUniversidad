<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PortafolioProveedorImagen extends Model
{
    use LogsActivity;

    protected $table = 'portafolio_proveedor_imagenes';

    protected $fillable = [
        'portafolio_proveedor_id',
        'imagen',
        'titulo',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function portafolioProveedor()
    {
        return $this->belongsTo(PortafolioProveedor::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('portafolio_proveedor_imagenes')
            ->logOnly(['portafolio_proveedor_id', 'imagen', 'titulo', 'descripcion', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
