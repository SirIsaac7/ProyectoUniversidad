<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TipoDocumentoProveedor extends Model
{
    use LogsActivity;

    protected $table = 'tipos_documento_proveedor';

    protected $fillable = [
        'nombre',
        'descripcion',
        'obligatorio',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'obligatorio' => 'boolean',
            'estado' => 'boolean',
        ];
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoProveedor::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('tipos_documento_proveedor')
            ->logOnly(['nombre', 'descripcion', 'obligatorio', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
