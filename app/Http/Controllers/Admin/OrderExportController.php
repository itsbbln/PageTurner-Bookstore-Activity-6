<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminOrdersExport;
use App\Exports\FinancialReportExport;
use App\Http\Controllers\Controller;
use App\Models\ExportLog;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class OrderExportController extends Controller
{
    public function exportOrders(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'status' => 'nullable|in:pending,processing,completed,cancelled',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'customer_id' => 'nullable|exists:users,id',
        ]);

        $format = $validated['format'];
        $filters = collect($validated)->except('format')->toArray();

        $query = Order::query();
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['customer_id'])) {
            $query->where('user_id', (int) $filters['customer_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        $count = $query->count();

        $log = ExportLog::create([
            'user_id' => $request->user()?->id,
            'type' => 'orders',
            'format' => $format,
            'filters' => $filters,
            'status' => 'running',
            'record_count' => $count,
            'started_at' => now(),
            'stored_disk' => 'local',
        ]);

        $filename = "orders_export_{$log->uuid}.{$format}";
        if ($format === 'pdf') {
            $orders = $query->with('user')->latest()->get();
            $pdf = Pdf::loadView('admin.orders.export-pdf', ['orders' => $orders, 'filters' => $filters]);
            $log->update(['status' => 'completed', 'finished_at' => now()]);

            return $pdf->download($filename);
        }

        $writer = $format === 'xlsx' ? ExcelFormat::XLSX : ExcelFormat::CSV;
        $log->update(['status' => 'completed', 'finished_at' => now()]);
        return Excel::download(new AdminOrdersExport($filters), $filename, $writer);
    }

    public function exportFinancial(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
        ]);

        $taxRate = (float) ($validated['tax_rate'] ?? 0.12);
        $format = $validated['format'];
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        $report = new FinancialReportExport($dateFrom, $dateTo, $taxRate);
        $filename = "financial_report_" . now()->format('Ymd_His') . ".{$format}";

        if ($format === 'pdf') {
            $rows = $report->collection();
            $pdf = Pdf::loadView('admin.orders.financial-pdf', [
                'rows' => $rows,
                'taxRate' => $taxRate,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
            return $pdf->download($filename);
        }

        $writer = $format === 'xlsx' ? ExcelFormat::XLSX : ExcelFormat::CSV;
        return Excel::download($report, $filename, $writer);
    }
}

