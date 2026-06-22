<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAspectoCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $aspectoCalificacion = $this->route('aspecto_calificacion');

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('aspectos_calificacion', 'nombre')->ignore($aspectoCalificacion?->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['required', 'boolean'],
        ];
    }
}
