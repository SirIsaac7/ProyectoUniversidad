<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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

        return view('admin.activitylogs.index', compact('activities'));
    }
}
