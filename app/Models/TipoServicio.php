<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TipoServicio extends Model
{
    use LogsActivity;

    protected $table = 'tipos_servicio';

    protected $fillable = [
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

    public function rubros()
    {
        return $this->belongsToMany(Rubro::class, 'rubro_tipo_servicio')
            ->withPivot('estado')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('tipos_servicio')
            ->logOnly(['nombre', 'descripcion', 'imagen', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
