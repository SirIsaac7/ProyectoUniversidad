<?php

namespace App\Http\Requests\Cliente;

use App\Models\AspectoCalificacion;
use App\Models\Cita;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cita_id' => ['required', 'integer', Rule::exists('citas', 'id')],
            'puntuacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:1500'],
            'aspectos' => ['nullable', 'array'],
            'aspectos.*' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('cita_id')) {
                return;
            }

            $cita = Cita::query()
                ->with('solicitud')
                ->withExists('calificacion')
                ->find($this->input('cita_id'));

            if (! $cita) {
                return;
            }

            if ((int) $cita->solicitud?->cliente_user_id !== (int) $this->user()?->id) {
                $validator->errors()->add('cita_id', 'La cita no pertenece a tu cuenta.');
            }

            if ($cita->estado !== 'completada') {
                $validator->errors()->add('cita_id', 'Solo puedes calificar citas completadas.');
            }

            if ($cita->calificacion_exists) {
                $validator->errors()->add('cita_id', 'Esta cita ya fue calificada.');
            }

            $aspectos = collect($this->input('aspectos', []))->keys()->map(fn ($id) => (int) $id);

            if ($aspectos->isEmpty()) {
                return;
            }

            $aspectosActivos = AspectoCalificacion::query()
                ->where('estado', true)
                ->whereIn('id', $aspectos)
                ->count();

            if ($aspectosActivos !== $aspectos->count()) {
                $validator->errors()->add('aspectos', 'Uno o más aspectos de calificación no están disponibles.');
            }
        });
    }
}
