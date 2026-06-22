<?php

namespace App\Services;

use App\Models\AspectoCalificacion;

class AspectoCalificacionService
{
    public function aspectosAdmin()
    {
        return AspectoCalificacion::query()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate(15);
    }

    public function aspectosActivos()
    {
        return AspectoCalificacion::query()
            ->where('estado', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }

    public function create(array $data): AspectoCalificacion
    {
        return AspectoCalificacion::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => $data['estado'],
            'orden' => $data['orden'] ?? $this->siguienteOrden(),
        ]);
    }

    public function update(AspectoCalificacion $aspectoCalificacion, array $data): AspectoCalificacion
    {
        $datos = [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => $data['estado'],
        ];

        if (array_key_exists('orden', $data)) {
            $datos['orden'] = $data['orden'];
        }

        $aspectoCalificacion->update($datos);

        return $aspectoCalificacion;
    }

    public function toggleEstado(AspectoCalificacion $aspectoCalificacion): AspectoCalificacion
    {
        $aspectoCalificacion->update([
            'estado' => ! $aspectoCalificacion->estado,
        ]);

        return $aspectoCalificacion;
    }

    protected function siguienteOrden(): int
    {
        return ((int) AspectoCalificacion::max('orden')) + 1;
    }
}
