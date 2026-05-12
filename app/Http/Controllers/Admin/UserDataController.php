<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\ExportLog;
use App\Models\ImportLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class UserDataController extends Controller
{
    public function downloadTemplate()
    {
        $headers = ['name', 'email', 'password', 'role'];
        $sample = ['Jane Doe', 'jane@example.com', 'secret123', 'customer'];
        $csv = implode(',', $headers) . PHP_EOL . implode(',', $sample) . PHP_EOL;

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_import_template.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt',
            'default_role' => 'nullable|in:admin,customer,premium',
        ]);

        $file = $validated['file'];
        $defaultRole = $validated['default_role'] ?? 'customer';

        $storedPath = $file->store('imports/users', 'local');

        $log = ImportLog::create([
            'user_id' => $request->user()?->id,
            'type' => 'users',
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'stored_disk' => 'local',
            'mime_type' => $file->getClientMimeType(),
            'status' => 'queued',
        ]);

        $import = new UsersImport($log->id, $defaultRole);
        Excel::queueImport($import, Storage::disk('local')->path($storedPath))->allOnQueue('imports');

        return redirect()->route('admin.books.data.import-logs.show', $log)
            ->with('success', 'User import queued.');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'redact_pii' => 'nullable|boolean',
            'role' => 'nullable|in:admin,customer,premium',
        ]);

        $format = $validated['format'];
        $redact = (bool) ($validated['redact_pii'] ?? false);
        $role = $validated['role'] ?? null;

        $count = User::query()->when($role, fn ($q) => $q->where('role', $role))->count();
        $log = ExportLog::create([
            'user_id' => $request->user()?->id,
            'type' => 'users',
            'format' => $format,
            'filters' => ['role' => $role, 'redact_pii' => $redact],
            'status' => 'running',
            'record_count' => $count,
            'started_at' => now(),
            'stored_disk' => 'local',
        ]);

        $filename = "users_export_{$log->uuid}.{$format}";
        $export = new UsersExport($redact, $role);

        if ($format === 'pdf') {
            $users = User::query()->when($role, fn ($q) => $q->where('role', $role))->latest()->get();
            $pdf = Pdf::loadView('admin.users.export-pdf', ['users' => $users, 'redact' => $redact]);
            $log->update(['status' => 'completed', 'finished_at' => now()]);
            return $pdf->download($filename);
        }

        $writer = $format === 'xlsx' ? ExcelFormat::XLSX : ExcelFormat::CSV;
        $log->update(['status' => 'completed', 'finished_at' => now()]);
        return Excel::download($export, $filename, $writer);
    }
}

