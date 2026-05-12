@extends('layouts.app')

@section('title', 'Audit Detail - PageTurner')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Audit Detail</h1>
            <p class="text-sm text-matcha-100">{{ $audit->event }} • {{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</p>
        </div>
        <a href="{{ route('admin.audit.index') }}" class="text-sm text-matcha-200 hover:text-white">← Back to Audit Logs</a>
    </div>
@endsection

@section('content')
    @php
        $old = is_array($audit->old_values) ? $audit->old_values : (json_decode($audit->old_values ?? '[]', true) ?: []);
        $new = is_array($audit->new_values) ? $audit->new_values : (json_decode($audit->new_values ?? '[]', true) ?: []);
        $keys = collect(array_unique(array_merge(array_keys($old), array_keys($new))))->sort()->values();
    @endphp

    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-sm text-gray-500">Time</div>
                <div class="font-semibold text-gray-900">{{ $audit->created_at?->toDayDateTimeString() }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">User</div>
                <div class="font-semibold text-gray-900">{{ $audit->user_id ? ('#' . $audit->user_id) : '—' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Checksum</div>
                <div class="font-mono text-xs text-gray-700 break-all">{{ $audit->checksum ?? '—' }}</div>
            </div>
        </div>

        <div>
            <h2 class="font-semibold text-gray-900">Diff</h2>
            <div class="mt-3 overflow-auto border border-gray-200 rounded-xl">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Field</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Old</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">New</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($keys as $k)
                            @php
                                $ov = $old[$k] ?? null;
                                $nv = $new[$k] ?? null;
                                $changed = json_encode($ov) !== json_encode($nv);
                            @endphp
                            <tr class="{{ $changed ? 'bg-amber-50' : '' }}">
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $k }}</td>
                                <td class="px-4 py-2 text-gray-700">
                                    <pre class="text-xs whitespace-pre-wrap">{{ is_scalar($ov) || $ov === null ? var_export($ov, true) : json_encode($ov, JSON_PRETTY_PRINT) }}</pre>
                                </td>
                                <td class="px-4 py-2 text-gray-700">
                                    <pre class="text-xs whitespace-pre-wrap">{{ is_scalar($nv) || $nv === null ? var_export($nv, true) : json_encode($nv, JSON_PRETTY_PRINT) }}</pre>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-6 text-center text-gray-500" colspan="3">No values recorded for this event.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

