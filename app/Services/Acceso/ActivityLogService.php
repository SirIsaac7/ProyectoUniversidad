<?php

namespace App\Services\Acceso;

use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function getAll()
    {
        return Activity::with(['causer', 'subject'])
            ->latest()
            ->get();
    }
}
