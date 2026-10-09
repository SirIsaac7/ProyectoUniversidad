<?php

namespace App\Policies;

use App\Models\EjecucionBusquedaMotor;
use App\Models\User;

class EjecucionBusquedaMotorPolicy
{
    public function view(User $user, EjecucionBusquedaMotor $ejecucion): bool
    {
        return $ejecucion->usuario_id === $user->id;
    }
}
