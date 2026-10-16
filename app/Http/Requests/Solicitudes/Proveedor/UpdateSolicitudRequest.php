<?php

namespace App\Http\Requests\Solicitudes\Proveedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['rechazada'])],
            'comentario' => ['nullable', 'required_if:estado,rechazada', 'string', 'max:1000'],
        ];
    }
}
