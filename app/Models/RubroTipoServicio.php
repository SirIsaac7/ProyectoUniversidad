<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RubroTipoServicio extends Pivot
{
    protected $table = 'rubro_tipo_servicio';

    public $incrementing = true;

    protected $fillable = [
        'rubro_id',
        'tipo_servicio_id',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function rubro()
    {
        return $this->belongsTo(Rubro::class);
    }

    public function tipoServicio()
    {
        return $this->belongsTo(TipoServicio::class);
    }

    public function especialidades()
    {
        return $this->hasMany(Especialidad::class);
    }
}
