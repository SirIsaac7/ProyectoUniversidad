<?php

namespace App\Http\Controllers\Acceso;

use App\Http\Controllers\Controller;

use App\Services\Acceso\ActivityLogService;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {
        $this->middleware('permission:ver activity logs');
    }

    public function index()
    {
        $activities = $this->activityLogService->getAll();

        return view('acceso.activity-logs.index', compact('activities'));
    }
}
