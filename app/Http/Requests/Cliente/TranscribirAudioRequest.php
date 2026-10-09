<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class TranscribirAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'audio' => [
                'required',
                'file',
                'max:15360',
                'mimetypes:audio/webm,video/webm,audio/ogg,application/ogg,audio/mp4,video/mp4,audio/mpeg,audio/wav,audio/x-wav',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'audio' => 'grabacion de voz',
        ];
    }
}
