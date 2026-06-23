<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ConfiguracionReporte extends Model
{
    use LogsActivity;

    protected $table = 'configuracion_reportes';

    protected $fillable = [
        'tamano_hoja',
        'orientacion',
        'logo_path',
        'color_principal',
        'titulo_encabezado',
        'texto_pie',
        'mostrar_logo',
        'mostrar_fecha',
        'mostrar_generado_por',
    ];

    protected function casts(): array
    {
        return [
            'mostrar_logo' => 'boolean',
            'mostrar_fecha' => 'boolean',
            'mostrar_generado_por' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('configuracion_reportes')
            ->logOnly([
                'tamano_hoja',
                'orientacion',
                'logo_path',
                'color_principal',
                'titulo_encabezado',
                'texto_pie',
                'mostrar_logo',
                'mostrar_fecha',
                'mostrar_generado_por',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
