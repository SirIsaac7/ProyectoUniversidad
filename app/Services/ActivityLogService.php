<?php

namespace App\Services;

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
