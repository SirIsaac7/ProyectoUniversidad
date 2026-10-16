<?php

namespace App\Http\Requests\BusquedaInteligente\Administracion;

use App\Services\BusquedaInteligente\Administracion\ConfiguracionBusquedaVisualService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonalizacionBusquedaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('modificar personalizacion busqueda') ?? false;
    }

    public function rules(): array
    {
        $colores = [
            'color_primario',
            'color_secundario',
            'color_boton',
            'color_fondo',
            'color_indicador',
            'color_fase_completada',
            'color_fase_actual',
            'color_informativo',
            'color_error',
            'color_destacado',
        ];
        $reglas = [];

        foreach ($colores as $color) {
            $reglas[$color] = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        $reglas += [
            'familia_tipografica' => ['required', Rule::in(['system', 'inter', 'poppins', 'roboto', 'arial'])],
            'tamano_titulos' => ['required', 'integer', 'between:18,36'],
            'tamano_mensajes' => ['required', 'integer', 'between:12,20'],
            'grosor_texto' => ['required', Rule::in([400, 500, 600, 700, 800])],
            'animacion' => ['required', Rule::in(['spinner', 'puntos', 'barra', 'etapas', 'busqueda'])],
            'presentacion_fases' => ['required', Rule::in(['actual', 'lista', 'linea', 'animacion'])],
            'diseno_tarjetas' => ['required', Rule::in(['clasica', 'compacta', 'destacada'])],
            'radio_bordes' => ['required', 'integer', 'between:0,24'],
            'espaciado' => ['required', Rule::in(['compacto', 'normal', 'amplio'])],
            'distribucion' => ['required', Rule::in(['estandar', 'detallada'])],
            'apariencia_calificacion' => ['required', Rule::in(['estrellas', 'insignia', 'texto'])],
            'apariencia_explicacion' => ['required', Rule::in(['caja', 'texto'])],
            'mensajes_fases' => ['required', 'array'],
        ];

        foreach (ConfiguracionBusquedaVisualService::FASES as $fase) {
            $reglas['mensajes_fases.'.$fase] = ['required', 'string', 'max:160', 'not_regex:/[<>]/'];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            '*.regex' => 'El color debe utilizar el formato hexadecimal #RRGGBB.',
            'mensajes_fases.*.not_regex' => 'Los mensajes no pueden contener etiquetas HTML.',
        ];
    }
}
