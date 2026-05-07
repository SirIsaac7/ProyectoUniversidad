<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEspecialidadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $especialidad = $this->route('especialidade');

        return [
            'rubro_tipo_servicio_id' => ['required', 'integer', 'exists:rubro_tipo_servicio,id'],
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('especialidades', 'nombre')
                    ->where('rubro_tipo_servicio_id', $this->input('rubro_tipo_servicio_id'))
                    ->ignore($especialidad),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }
}
