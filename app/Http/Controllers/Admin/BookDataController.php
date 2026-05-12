<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\ExportLog;
use App\Models\ImportLog;
use App\Exports\BooksExport;
use App\Imports\BooksImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class BookDataController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $importLogs = ImportLog::where('type', 'books')
            ->latest()
            ->paginate(5, ['*'], 'import_page');

        $exportLogs = ExportLog::where('type', 'books')
            ->latest()
            ->paginate(5, ['*'], 'export_page');

        $defaultColumns = [
            'isbn' => true,
            'title' => true,
            'author' => true,
            'price' => true,
            'stock_quantity' => true,
            'category' => true,
            'description' => false,
            'created_at' => false,
        ];

        return view('admin.books.data', compact('categories', 'importLogs', 'exportLogs', 'defaultColumns'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'ISBN',
            'Title',
            'Author',
            'Price',
            'Stock',
            'Category',
            'Description',
        ];

        $csv = implode(',', array_map(fn ($h) => '"' . str_replace('"', '""', $h) . '"', $headers)) . "\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pageturner_books_import_template.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt',
            'update_existing' => 'nullable|boolean',
            'allow_duplicate_file' => 'nullable|boolean',
        ]);

        $file = $validated['file'];
        $updateExisting = (bool) ($validated['update_existing'] ?? false);
        $allowDuplicateFile = (bool) ($validated['allow_duplicate_file'] ?? false);
        $fileHash = hash_file('sha256', $file->getRealPath());

        $existingImport = ImportLog::where('type', 'books')
            ->where('file_hash', $fileHash)
            ->latest()
            ->first();

        if ($existingImport && ! $allowDuplicateFile) {
            return back()
                ->withErrors([
                    'file' => 'This file already exists. Check "Allow duplicate file upload" if you want to update/re-import it.',
                ])
                ->withInput();
        }

        $storedPath = $file->store('imports/books', 'local');

        $log = ImportLog::create([
            'user_id' => $request->user()?->id,
            'type' => 'books',
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'stored_disk' => 'local',
            'mime_type' => $file->getClientMimeType(),
            'file_hash' => $fileHash,
            'update_existing' => $updateExisting,
            'status' => 'queued',
            'failures' => null,
        ]);

        $import = new BooksImport(importLogId: $log->id, updateExisting: $updateExisting);

        // Always queue; if QUEUE_CONNECTION=sync it will run immediately.
        Excel::queueImport($import, Storage::disk('local')->path($storedPath))
            ->allOnQueue('imports');

        return redirect()
            ->route('admin.books.data.import-logs.show', $log)
            ->with('success', 'Import queued. Refresh to see progress.');
    }

    public function showImportLog(ImportLog $importLog)
    {
        abort_unless($importLog->type === 'books', 404);

        return view('admin.books.import-log', [
            'importLog' => $importLog->fresh(),
        ]);
    }

    public function downloadImportFailureReport(ImportLog $importLog)
    {
        abort_unless($importLog->type === 'books', 404);

        if (! $importLog->failure_report_path) {
            abort(404);
        }

        return Storage::disk($importLog->stored_disk)->download($importLog->failure_report_path);
    }

    public function destroyImportLog(ImportLog $importLog)
    {
        abort_unless($importLog->type === 'books', 404);

        // Avoid deleting a log while import may still be in progress.
        if (in_array($importLog->status, ['queued', 'running'], true)) {
            return back()->with('error', 'Cannot delete an import that is still in progress.');
        }

        $disk = $importLog->stored_disk ?: 'local';

        if ($importLog->stored_path && Storage::disk($disk)->exists($importLog->stored_path)) {
            Storage::disk($disk)->delete($importLog->stored_path);
        }

        if ($importLog->failure_report_path && Storage::disk($disk)->exists($importLog->failure_report_path)) {
            Storage::disk($disk)->delete($importLog->failure_report_path);
        }

        $importLog->delete();

        return redirect()
            ->route('admin.books.data.index')
            ->with('success', 'Import log deleted successfully.');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'category_id' => 'nullable|exists:categories,id',
            'min_price' => 'nullable|numeric|min:0|max:9999.99',
            'max_price' => 'nullable|numeric|min:0|max:9999.99',
            'stock_status' => 'nullable|in:in_stock,out_of_stock,low_stock',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'columns' => 'nullable|array',
            'columns.*' => 'string',
        ]);

        $format = $validated['format'];
        $filters = collect($validated)->except(['format', 'columns'])->toArray();
        $columns = $validated['columns'] ?? [];

        $countQuery = Book::query();
        if (! empty($filters['category_id'])) {
            $countQuery->where('category_id', $filters['category_id']);
        }
        if (isset($filters['min_price'])) {
            $countQuery->where('price', '>=', (float) $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $countQuery->where('price', '<=', (float) $filters['max_price']);
        }
        if (! empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'in_stock') {
                $countQuery->where('stock_quantity', '>', 0);
            } elseif ($filters['stock_status'] === 'out_of_stock') {
                $countQuery->where('stock_quantity', '=', 0);
            } elseif ($filters['stock_status'] === 'low_stock') {
                $countQuery->whereBetween('stock_quantity', [1, 5]);
            }
        }
        if (! empty($filters['date_from'])) {
            $countQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $countQuery->whereDate('created_at', '<=', $filters['date_to']);
        }
        $recordCount = $countQuery->count();

        // Queue for very large exports; otherwise download immediately.
        $shouldQueue = $recordCount > 10000;

        $log = ExportLog::create([
            'user_id' => $request->user()?->id,
            'type' => 'books',
            'format' => $format,
            'filters' => $filters,
            'columns' => $columns,
            'status' => $shouldQueue ? 'queued' : 'running',
            'record_count' => $recordCount,
            'stored_disk' => 'local',
            'stored_path' => null,
            'started_at' => now(),
        ]);

        $export = new BooksExport(filters: $filters, columns: $columns, exportLogId: $log->id);

        $writerType = match ($format) {
            'xlsx' => ExcelFormat::XLSX,
            'csv' => ExcelFormat::CSV,
            'pdf' => ExcelFormat::DOMPDF,
        };

        $filename = "exports/books/books_export_{$log->uuid}.{$format}";

        $log->update([
            'stored_path' => $filename,
        ]);

        if ($shouldQueue) {
            Excel::queue($export, $filename, 'local', $writerType)
                ->allOnQueue('exports');

            return redirect()
                ->route('admin.books.data.export-logs.show', $log)
                ->with('success', 'Export queued. You will be redirected to download when ready.');
        }

        // Synchronous export: generate and immediately download to user's device.
        Excel::store($export, $filename, 'local', $writerType);

        $log->update([
            'status' => 'completed',
            'finished_at' => now(),
        ]);

        return Storage::disk('local')->download($filename);
    }

    public function showExportLog(ExportLog $exportLog)
    {
        abort_unless($exportLog->type === 'books', 404);

        return view('admin.books.export-log', [
            'exportLog' => $exportLog->fresh(),
        ]);
    }

    public function downloadExport(ExportLog $exportLog)
    {
        abort_unless($exportLog->type === 'books', 404);

        if (! $exportLog->stored_path || ! Storage::disk($exportLog->stored_disk)->exists($exportLog->stored_path)) {
            abort(404);
        }

        return Storage::disk($exportLog->stored_disk)->download($exportLog->stored_path);
    }
}

