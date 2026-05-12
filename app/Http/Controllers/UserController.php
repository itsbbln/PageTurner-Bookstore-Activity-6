<?php

namespace App\Http\Controllers;

use App\Models\ExportLog;
use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin only).
     */
    public function index()
    {
        $users = User::withCount(['orders', 'reviews'])
            ->latest()
            ->paginate(5);

        $recentUserImports = ImportLog::query()->where('type', 'users')
            ->latest()
            ->paginate(5, ['*'], 'import_page');

        $recentUserExports = ExportLog::query()->where('type', 'users')
            ->latest()
            ->paginate(5, ['*'], 'export_page');

        $recentOrderExports = ExportLog::query()->whereIn('type', ['orders', 'financial'])
            ->latest()
            ->paginate(5, ['*'], 'order_export_page');

        return view('admin.users.index', compact('users', 'recentUserImports', 'recentUserExports', 'recentOrderExports'));
    }
}
