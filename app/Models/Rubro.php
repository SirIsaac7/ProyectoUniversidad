<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Rubro extends Model
{
    use LogsActivity;

    protected $table = 'rubros';

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('rubros')
            ->logOnly(['nombre', 'descripcion', 'imagen', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
