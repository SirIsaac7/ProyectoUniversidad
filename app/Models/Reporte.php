<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reporte extends Model
{
    use LogsActivity;

    protected $table = 'reportes';

    protected $fillable = [
        'user_id',
        'nombre',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'incluir_graficas',
        'incluir_imagenes',
        'estado',
        'opciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'incluir_graficas' => 'boolean',
            'incluir_imagenes' => 'boolean',
            'estado' => 'boolean',
            'opciones' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('reportes')
            ->logOnly([
                'user_id',
                'nombre',
                'tipo',
                'fecha_inicio',
                'fecha_fin',
                'incluir_graficas',
                'incluir_imagenes',
                'estado',
                'opciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
