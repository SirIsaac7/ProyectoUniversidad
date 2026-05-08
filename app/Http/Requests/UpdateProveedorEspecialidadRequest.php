<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProveedorEspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $proveedorEspecialidadId = $this->route('proveedor_especialidade');

        return [
            'perfil_proveedor_id' => [
                'required',
                'integer',
                Rule::exists('perfiles_proveedores', 'id')->where('estado', true),
            ],
            'especialidad_id' => [
                'required',
                'integer',
                Rule::exists('especialidades', 'id')->where('estado', true),
                Rule::unique('proveedor_especialidad', 'especialidad_id')
                    ->where('perfil_proveedor_id', $this->input('perfil_proveedor_id'))
                    ->ignore($proveedorEspecialidadId),
            ],
            'es_principal' => ['required', 'boolean'],
        ];
    }
}
