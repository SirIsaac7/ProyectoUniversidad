<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionBusquedaVisual extends Model
{
    protected $table = 'configuraciones_busqueda_visual';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'mensajes_fases' => 'array',
            'tamano_titulos' => 'integer',
            'tamano_mensajes' => 'integer',
            'grosor_texto' => 'integer',
            'radio_bordes' => 'integer',
        ];
    }
}
