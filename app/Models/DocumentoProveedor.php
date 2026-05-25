<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentoProveedor extends Model
{
    use LogsActivity;

    protected $table = 'documentos_proveedor';

    protected $fillable = [
        'perfil_proveedor_id',
        'tipo_documento_proveedor_id',
        'archivo',
        'estado_revision',
        'observacion',
        'fecha_revision',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_revision' => 'datetime',
            'estado' => 'boolean',
        ];
    }

    public function perfilProveedor()
    {
        return $this->belongsTo(PerfilProveedor::class);
    }

    public function tipoDocumentoProveedor()
    {
        return $this->belongsTo(TipoDocumentoProveedor::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('documentos_proveedor')
            ->logOnly([
                'perfil_proveedor_id',
                'tipo_documento_proveedor_id',
                'archivo',
                'estado_revision',
                'observacion',
                'fecha_revision',
                'estado',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
