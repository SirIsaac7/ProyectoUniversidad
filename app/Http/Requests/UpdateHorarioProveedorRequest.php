<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHorarioProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $horarioProveedorId = $this->route('horarios_proveedor');

        return [
            'perfil_proveedor_id' => [
                'required',
                'integer',
                Rule::exists('perfiles_proveedores', 'id')->where('estado', true),
            ],
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => [
                'required',
                'date_format:H:i',
                Rule::unique('horarios_proveedor', 'hora_inicio')
                    ->where('perfil_proveedor_id', $this->input('perfil_proveedor_id'))
                    ->where('dia_semana', $this->input('dia_semana'))
                    ->where('hora_fin', $this->input('hora_fin'))
                    ->ignore($horarioProveedorId),
            ],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo_atencion' => ['required', 'string', Rule::in(['domicilio', 'local', 'remoto', 'mixto'])],
            'disponible' => ['required', 'boolean'],
            'estado' => ['required', 'boolean'],
        ];
    }
}
