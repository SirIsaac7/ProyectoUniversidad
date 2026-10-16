<?php

namespace App\Http\Requests\Calificaciones\Administracion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['visible', 'oculta', 'pendiente_revision'])],
        ];
    }
}
