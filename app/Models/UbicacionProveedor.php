<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class UbicacionProveedor extends Model
{
    use LogsActivity;

    protected $table = 'ubicaciones_proveedor';

    protected $fillable = [
        'perfil_proveedor_id',
        'zona',
        'direccion',
        'latitud',
        'longitud',
        'radio_cobertura_km',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'radio_cobertura_km' => 'integer',
        ];
    }

    public function perfilProveedor()
    {
        return $this->belongsTo(PerfilProveedor::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ubicaciones_proveedor')
            ->logOnly(['perfil_proveedor_id', 'zona', 'direccion', 'latitud', 'longitud', 'radio_cobertura_km'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
