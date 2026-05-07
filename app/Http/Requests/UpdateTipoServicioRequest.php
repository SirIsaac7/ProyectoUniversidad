<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoServicioRequest extends FormRequest
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
        $tipoServicio = $this->route('tipos_servicio');

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipos_servicio', 'nombre')->ignore($tipoServicio),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'rubros' => ['required', 'array', 'min:1'],
            'rubros.*' => ['exists:rubros,id'],
        ];
    }
}
