@extends('layouts.app')

@section('title', 'Data Management - PageTurner')

@section('header')
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white">Data Management</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-matcha-200 hover:text-white">← Back to Dashboard</a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <div class="text-sm font-medium text-gray-500 uppercase tracking-wide">Backup Status</div>
            <div class="mt-2 text-lg font-bold {{ ($backup->status ?? null) === 'failed' ? 'text-red-700' : 'text-emerald-700' }}">
                {{ $backup ? strtoupper($backup->status) : 'N/A' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ $backup->executed_at ?? 'No backup log yet' }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500 uppercase tracking-wide">Task Failures (7d)</div>
            <div class="mt-2 text-3xl font-bold text-matcha-900">{{ $taskFailures7d }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500 uppercase tracking-wide">Rate Limit Hits (24h)</div>
            <div class="mt-2 text-3xl font-bold text-matcha-900">{{ $rateLimitHits24h }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-3">Quick Actions</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.books.data.index') }}" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-matcha-800 text-white hover:bg-matcha-900">
                Books import/export
            </a>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">
                Users import/export
            </a>
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800">
                Order exports
            </a>
            <a href="{{ route('admin.audit.index') }}" class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold bg-purple-600 text-white hover:bg-purple-700">
                Audit logs & export
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Recent Book Imports</h2>
            <div class="space-y-2 text-sm text-gray-700">
                @forelse($recentBookImports as $log)
                    <div class="flex items-center justify-between gap-3">
                        <div class="truncate">
                            <span class="font-semibold">{{ $log->status }}</span>
                            <span class="text-gray-500">— {{ $log->original_filename }}</span>
                        </div>
                        <a class="text-indigo-600 hover:text-indigo-800 shrink-0" href="{{ route('admin.books.data.import-logs.show', $log) }}">View</a>
                    </div>
                @empty
                    <div class="text-gray-500">No records yet.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Recent Book Exports</h2>
            <div class="space-y-2 text-sm text-gray-700">
                @forelse($recentBookExports as $log)
                    <div class="flex items-center justify-between gap-3">
                        <div class="truncate">
                            <span class="font-semibold">{{ $log->status }}</span>
                            <span class="text-gray-500">— {{ strtoupper($log->format) }}</span>
                        </div>
                        <a class="text-indigo-600 hover:text-indigo-800 shrink-0" href="{{ route('admin.books.data.export-logs.show', $log) }}">View</a>
                    </div>
                @empty
                    <div class="text-gray-500">No records yet.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Recent User Imports</h2>
            <div class="space-y-2 text-sm text-gray-700">
                @forelse($recentUserImports as $log)
                    <div class="flex items-center justify-between gap-3">
                        <div class="truncate">
                            <span class="font-semibold">{{ $log->status }}</span>
                            <span class="text-gray-500">— {{ $log->original_filename }}</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $log->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                @empty
                    <div class="text-gray-500">No records yet.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Recent User/Order Exports</h2>
            <div class="space-y-2 text-sm text-gray-700">
                @forelse($recentUserExports as $log)
                    <div class="flex items-center justify-between gap-3">
                        <div class="truncate">
                            <span class="font-semibold">users</span>
                            <span class="text-gray-500">— {{ $log->status }} ({{ strtoupper($log->format) }})</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $log->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                @empty
                    <div class="text-gray-500">No user exports yet.</div>
                @endforelse

                @foreach($recentOrderExports as $log)
                    <div class="flex items-center justify-between gap-3">
                        <div class="truncate">
                            <span class="font-semibold">{{ $log->type }}</span>
                            <span class="text-gray-500">— {{ $log->status }} ({{ strtoupper($log->format) }})</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $log->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Recent Audit Events</h2>
            <div class="space-y-2 text-sm text-gray-700">
                @forelse($recentAudits as $a)
                    <div class="flex items-center justify-between gap-3">
                        <div class="truncate">
                            <span class="font-semibold">{{ $a->event }}</span>
                            <span class="text-gray-500">— {{ class_basename($a->auditable_type) }} #{{ $a->auditable_id }}</span>
                        </div>
                        <a class="text-indigo-600 hover:text-indigo-800 shrink-0" href="{{ route('admin.audit.show', $a) }}">View</a>
                    </div>
                @empty
                    <div class="text-gray-500">No audit events yet.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

