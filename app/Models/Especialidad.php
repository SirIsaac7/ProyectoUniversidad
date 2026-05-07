<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Especialidad extends Model
{
    use LogsActivity;

    protected $table = 'especialidades';

    protected $fillable = [
        'rubro_tipo_servicio_id',
        'nombre',
        'descripcion',
        'imagen',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function rubroTipoServicio()
    {
        return $this->belongsTo(RubroTipoServicio::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('especialidades')
            ->logOnly(['rubro_tipo_servicio_id', 'nombre', 'descripcion', 'imagen', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
