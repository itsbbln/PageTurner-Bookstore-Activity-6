@extends('layouts.app')

@section('title', 'Import Log - PageTurner')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Import Log</h1>
            <p class="text-sm text-matcha-100">{{ $importLog->original_filename }}</p>
        </div>
        <a href="{{ route('admin.books.data.index') }}" class="text-sm text-matcha-200 hover:text-white">← Back to Data Management</a>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 p-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <div class="text-sm text-gray-500">Status</div>
                <div class="mt-1">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold
                        @if($importLog->status === 'completed') bg-green-100 text-green-800
                        @elseif($importLog->status === 'completed_with_errors') bg-amber-100 text-amber-800
                        @elseif($importLog->status === 'failed') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif
                    ">
                        {{ str_replace('_',' ', $importLog->status) }}
                    </span>
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Rows</div>
                <div class="text-lg font-semibold text-gray-900 mt-1">
                    {{ $importLog->processed_rows }} processed
                    @if ($importLog->total_rows)
                        / {{ $importLog->total_rows }} total
                    @endif
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Failures</div>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ $importLog->failed_rows }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Update existing</div>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ $importLog->update_existing ? 'Yes' : 'No' }}</div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.books.data.import-logs.show', $importLog) }}" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-gray-100 text-gray-900 hover:bg-gray-200">Refresh</a>
            @if ($importLog->failure_report_path)
                <a href="{{ route('admin.books.data.import-logs.failure-report', $importLog) }}" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-red-50 text-red-700 hover:bg-red-100">Download failure report</a>
            @endif
            @if (!in_array($importLog->status, ['queued', 'running']))
                <form method="POST" action="{{ route('admin.books.data.import-logs.destroy', $importLog) }}"
                      onsubmit="return confirm('Delete this import log and its files? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-red-600 text-white hover:bg-red-700">
                        Delete import
                    </button>
                </form>
            @endif
            <div class="text-xs text-gray-500 sm:ml-auto">
                UUID: <span class="font-mono">{{ $importLog->uuid }}</span>
            </div>
        </div>

        @if (!empty($importLog->failures))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <details>
                    <summary class="cursor-pointer list-none">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="font-semibold text-amber-900">Sample failures (up to 200)</h2>
                                <p class="text-xs text-amber-800 mt-1">
                                    This import has validation errors. Click to view row-by-row failure reasons.
                                </p>
                            </div>
                            <span class="text-xs font-semibold text-amber-900">View details</span>
                        </div>
                    </summary>

                    <div class="mt-4 overflow-auto border border-amber-200 rounded-xl bg-white">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Field</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Why it failed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($importLog->failures as $failure)
                                    <tr>
                                        <td class="px-4 py-2">{{ $failure['row'] ?? '—' }}</td>
                                        <td class="px-4 py-2">{{ $failure['attribute'] ?? '—' }}</td>
                                        <td class="px-4 py-2">
                                            @if (!empty($failure['errors']))
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($failure['errors'] as $err)
                                                        <li>{{ $err }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        @endif
    </div>
@endsection

