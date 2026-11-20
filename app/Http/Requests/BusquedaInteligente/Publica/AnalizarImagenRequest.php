<?php

namespace App\Http\Requests\BusquedaInteligente\Publica;

use Illuminate\Foundation\Http\FormRequest;

class AnalizarImagenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];
    }

    public function attributes(): array
    {
        return [
            'imagen' => 'imagen del problema',
        ];
    }
}
