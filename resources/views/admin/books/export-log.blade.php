@extends('layouts.app')

@section('title', 'Export Log - PageTurner')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Export Log</h1>
            <p class="text-sm text-matcha-100">Books • {{ strtoupper($exportLog->format) }}</p>
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
                        @if($exportLog->status === 'completed') bg-green-100 text-green-800
                        @elseif($exportLog->status === 'failed') bg-red-100 text-red-800
                        @elseif($exportLog->status === 'running') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif
                    ">
                        {{ $exportLog->status }}
                    </span>
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Records</div>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ $exportLog->record_count ?? '—' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Queued at</div>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ $exportLog->created_at->format('M d, Y H:i') }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">File</div>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ $exportLog->stored_path ? basename($exportLog->stored_path) : '—' }}</div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.books.data.export-logs.show', $exportLog) }}" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-gray-100 text-gray-900 hover:bg-gray-200">Refresh</a>
            @if ($exportLog->stored_path && $exportLog->status === 'completed')
                <a href="{{ route('admin.books.data.export-logs.download', $exportLog) }}" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-green-50 text-green-700 hover:bg-green-100">Download</a>
            @endif
            <div class="text-xs text-gray-500 sm:ml-auto">
                UUID: <span class="font-mono">{{ $exportLog->uuid }}</span>
            </div>
        </div>

        @if (!empty($exportLog->filters))
            <div>
                <h2 class="font-semibold text-gray-900">Filters</h2>
                <pre class="mt-2 p-3 rounded bg-gray-50 border border-gray-200 text-xs overflow-auto">{{ json_encode($exportLog->filters, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>

    @if ($exportLog->status === 'running' || $exportLog->status === 'queued')
        @push('scripts')
            <script>
                setTimeout(() => window.location.reload(), 3000);
            </script>
        @endpush
    @endif

    @if ($exportLog->status === 'completed' && $exportLog->stored_path)
        @push('scripts')
            <script>
                // Auto-download when ready
                window.location.href = @json(route('admin.books.data.export-logs.download', $exportLog));
            </script>
        @endpush
    @endif
@endsection

