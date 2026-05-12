<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = Audit::query()->latest();

        if ($request->filled('user_id')) {
            $q->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('event')) {
            $q->where('event', $request->string('event'));
        }
        if ($request->filled('auditable_type')) {
            $q->where('auditable_type', $request->string('auditable_type'));
        }
        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $audits = $q->paginate(5)->appends($request->query());

        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $eventOptions = ['created', 'updated', 'deleted', 'restored'];
        $typeOptions = Audit::query()->select('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type');

        return view('admin.audit.index', compact('audits', 'users', 'eventOptions', 'typeOptions'));
    }

    public function show(Audit $audit)
    {
        return view('admin.audit.show', [
            'audit' => $audit,
        ]);
    }

    public function details(Audit $audit)
    {
        $old = is_array($audit->old_values) ? $audit->old_values : (json_decode($audit->old_values ?? '[]', true) ?: []);
        $new = is_array($audit->new_values) ? $audit->new_values : (json_decode($audit->new_values ?? '[]', true) ?: []);
        $keys = collect(array_unique(array_merge(array_keys($old), array_keys($new))))->sort()->values();

        $diff = [];
        foreach ($keys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;
            
            $diff[$k] = [
                'old' => is_scalar($ov) || $ov === null ? var_export($ov, true) : json_encode($ov, JSON_PRETTY_PRINT),
                'new' => is_scalar($nv) || $nv === null ? var_export($nv, true) : json_encode($nv, JSON_PRETTY_PRINT),
            ];
        }

        return response()->json([
            'id' => $audit->id,
            'event' => strtoupper($audit->event),
            'user_name' => optional($audit->user)->name,
            'formatted_date' => $audit->created_at?->toDayDateTimeString(),
            'ip_address' => $audit->ip_address,
            'url' => $audit->url,
            'user_agent' => $audit->user_agent,
            'diff' => $diff
        ]);
    }

    public function exportCsv(Request $request)
    {
        $audits = $this->filteredAudits($request)->limit(5000)->get();

        $csv = implode(',', ['id', 'user_id', 'event', 'auditable_type', 'auditable_id', 'ip_address', 'url', 'created_at']) . PHP_EOL;
        foreach ($audits as $audit) {
            $csv .= implode(',', [
                $audit->id,
                $audit->user_id,
                '"' . str_replace('"', '""', (string) $audit->event) . '"',
                '"' . str_replace('"', '""', (string) $audit->auditable_type) . '"',
                $audit->auditable_id,
                '"' . str_replace('"', '""', (string) $audit->ip_address) . '"',
                '"' . str_replace('"', '""', (string) $audit->url) . '"',
                optional($audit->created_at)?->toDateTimeString(),
            ]) . PHP_EOL;
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_export_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $audits = $this->filteredAudits($request)->limit(2000)->get();
        $pdf = Pdf::loadView('admin.audit.export-pdf', ['audits' => $audits]);

        return $pdf->download('audit_export_' . now()->format('Ymd_His') . '.pdf');
    }

    private function filteredAudits(Request $request)
    {
        $q = Audit::query()->latest();
        if ($request->filled('user_id')) {
            $q->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('event')) {
            $q->where('event', $request->string('event'));
        }
        if ($request->filled('auditable_type')) {
            $q->where('auditable_type', $request->string('auditable_type'));
        }
        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return $q;
    }
}

