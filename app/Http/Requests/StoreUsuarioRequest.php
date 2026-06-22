<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUsuarioRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'celular' => ['nullable', 'digits:8'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'recibe_notificaciones_whatsapp' => ['nullable', 'boolean'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'estado' => ['required', 'boolean'],
        ];
    }
}
