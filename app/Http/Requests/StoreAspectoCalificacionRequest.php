<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAspectoCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:aspectos_calificacion,nombre'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['required', 'boolean'],
        ];
    }
}
