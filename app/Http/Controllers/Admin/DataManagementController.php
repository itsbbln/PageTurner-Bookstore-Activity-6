<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\ExportLog;
use App\Models\ImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataManagementController extends Controller
{
    public function index()
    {
        $recentBookImports = ImportLog::query()->where('type', 'books')->latest()->take(8)->get();
        $recentUserImports = ImportLog::query()->where('type', 'users')->latest()->take(8)->get();

        $recentBookExports = ExportLog::query()->where('type', 'books')->latest()->take(8)->get();
        $recentUserExports = ExportLog::query()->where('type', 'users')->latest()->take(8)->get();
        $recentOrderExports = ExportLog::query()->whereIn('type', ['orders', 'financial'])->latest()->take(8)->get();

        $recentAudits = Audit::query()->latest()->take(8)->get();

        $backup = null;
        $taskFailures7d = 0;
        $rateLimitHits24h = 0;

        if (Schema::hasTable('backup_monitoring')) {
            $backup = DB::table('backup_monitoring')->latest('executed_at')->first();
        }
        if (Schema::hasTable('scheduled_tasks')) {
            $taskFailures7d = DB::table('scheduled_tasks')
                ->where('status', 'failed')
                ->where('executed_at', '>=', now()->subDays(7))
                ->count();
        }
        if (Schema::hasTable('api_rate_limits')) {
            $rateLimitHits24h = DB::table('api_rate_limits')
                ->where('hit_at', '>=', now()->subDay())
                ->count();
        }

        return view('admin.data-management.index', compact(
            'recentBookImports',
            'recentUserImports',
            'recentBookExports',
            'recentUserExports',
            'recentOrderExports',
            'recentAudits',
            'backup',
            'taskFailures7d',
            'rateLimitHits24h',
        ));
    }
}

