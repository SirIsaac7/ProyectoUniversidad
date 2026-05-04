<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

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

        return view('activitylogs.index', compact('activities'));
    }
}
