<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApiIntegrationLog;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    public function activityLogs(): View
    {
        $logs = ActivityLog::query()->with('user')->latest('created_at')->paginate(25);

        return view('admin.management.logs.activity', compact('logs'));
    }

    public function apiLogs(): View
    {
        $logs = ApiIntegrationLog::query()->latest('created_at')->paginate(25);

        return view('admin.management.logs.api', compact('logs'));
    }

    public function securityLogs(): View
    {
        $logs = ActivityLog::query()->with('user')
            ->where(function ($query) {
                $query->where('module', 'auth')
                    ->orWhere('action', 'like', '%login%')
                    ->orWhere('action', 'like', '%suspend%')
                    ->orWhere('action', 'like', '%password%')
                    ->orWhere('module', 'security');
            })
            ->latest('created_at')
            ->paginate(25);

        return view('admin.management.logs.security', compact('logs'));
    }
}
