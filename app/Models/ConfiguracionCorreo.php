<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ConfiguracionCorreo extends Model
{
    use LogsActivity;

    protected $table = 'configuraciones_correo';

    protected $fillable = [
        'nombre_marca',
        'color_primario',
        'logo_path',
        'verificacion_asunto',
        'verificacion_titulo',
        'verificacion_mensaje',
        'verificacion_boton',
        'recuperacion_asunto',
        'recuperacion_titulo',
        'recuperacion_mensaje',
        'recuperacion_boton',
        'backup_destinatarios',
        'backup_notificar_exito',
        'backup_notificar_error',
        'backup_adjuntar_archivo',
        'backup_asunto_exito',
        'backup_mensaje_exito',
        'backup_asunto_error',
        'backup_mensaje_error',
    ];

    protected function casts(): array
    {
        return [
            'backup_destinatarios' => 'array',
            'backup_notificar_exito' => 'boolean',
            'backup_notificar_error' => 'boolean',
            'backup_adjuntar_archivo' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('configuracion_correos')
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
