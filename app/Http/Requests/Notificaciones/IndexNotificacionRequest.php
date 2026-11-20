<?php

namespace App\Http\Requests\Notificaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexNotificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ver mis notificaciones') ?? false;
    }

    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['todas', 'no_leidas', 'leidas'])],
            'tipo' => ['nullable', Rule::in(['todos', 'info', 'success', 'warning', 'danger', 'error'])],
            'orden' => ['nullable', Rule::in(['recientes', 'antiguas'])],
            'por_pagina' => ['nullable', 'integer', Rule::in([10, 20, 50])],
        ];
    }
}
