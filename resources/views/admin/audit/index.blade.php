@extends('layouts.app')

@section('title', 'Audit Logs - PageTurner')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Audit Logs</h1>
            <p class="text-sm text-matcha-100">Search and filter sensitive system changes</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-matcha-200 hover:text-white">← Back to Dashboard</a>
    </div>
@endsection

@section('content')
    <div x-data="{ 
        open: false, 
        audit: {}, 
        loading: false,
        async showAudit(id) {
            this.loading = true;
            this.open = true;
            try {
                const response = await fetch(`/admin/audit/${id}/details`);
                const data = await response.json();
                this.audit = data;
            } catch (e) {
                console.error('Failed to fetch audit details', e);
            } finally {
                this.loading = false;
            }
        }
    }">
        {{-- Filter Section --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Filter Audit Logs</h2>
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- User Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select name="user_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-matcha-500 focus:ring-matcha-500 text-sm">
                        <option value="">Any User</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Event Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                    <select name="event" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-matcha-500 focus:ring-matcha-500 text-sm">
                        <option value="">Any Event</option>
                        @foreach ($eventOptions as $ev)
                            <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ ucfirst($ev) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Model Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Model</label>
                    <select name="auditable_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-matcha-500 focus:ring-matcha-500 text-sm">
                        <option value="">Any Model</option>
                        @foreach ($typeOptions as $t)
                            <option value="{{ $t }}" @selected(request('auditable_type') === $t)>{{ class_basename($t) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range Fields -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-matcha-500 focus:ring-matcha-500 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-matcha-500 focus:ring-matcha-500 text-sm" />
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-lg px-4 py-2 bg-matcha-800 text-white font-semibold text-sm hover:bg-matcha-900 transition">
                        Filter Results
                    </button>
                    <a href="{{ route('admin.audit.index') }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 bg-gray-100 text-gray-700 font-semibold text-sm hover:bg-gray-200 transition">
                        Reset
                    </a>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex gap-2">
                    <a href="{{ route('admin.audit.export.csv', request()->query()) }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 transition">
                        Export CSV
                    </a>
                    <a href="{{ route('admin.audit.export.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 transition">
                        Export PDF
                    </a>
                </div>
                <div class="text-sm text-gray-500 italic">
                    Showing {{ $audits->count() }} records on this page
                </div>
            </div>
        </form>
    </div>

    {{-- Audit Logs Table --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Target Resource</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($audits as $audit)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $audit->created_at?->format('M d, Y') }}<br>
                                <span class="text-xs text-gray-400">{{ $audit->created_at?->format('H:i:s') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                @if($audit->user)
                                    {{ $audit->user->name }}
                                    <div class="text-xs text-gray-500 font-normal">ID: #{{ $audit->user_id }}</div>
                                @else
                                    <span class="text-gray-400">System / Guest</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                    @if($audit->event === 'created') bg-green-100 text-green-800
                                    @elseif($audit->event === 'updated') bg-blue-100 text-blue-800
                                    @elseif($audit->event === 'deleted') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif
                                ">{{ $audit->event }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="font-semibold">{{ class_basename($audit->auditable_type) }}</div>
                                <div class="text-xs text-gray-400">ID: #{{ $audit->auditable_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $audit->ip_address ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="showAudit({{ $audit->id }})" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-10 text-center text-gray-500 italic" colspan="6">
                                No audit logs match your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($audits->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $audits->links() }}
            </div>
        @endif
    </div>

    {{-- Audit Detail Modal --}}
    <div x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-cloak
         @keydown.escape.window="open = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity" 
                 aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                
                <div class="bg-matcha-800 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Audit Transaction Details</h3>
                    <button @click="open = false" class="text-white hover:text-gray-200 transition focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-6 max-h-[75vh] overflow-y-auto">
                    <div x-show="loading" class="flex items-center justify-center py-16">
                        <svg class="animate-spin h-10 w-10 text-matcha-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <div x-show="!loading" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-5 rounded-xl border border-gray-100 shadow-sm">
                            <div>
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Time</div>
                                <div class="mt-1 text-sm font-bold text-gray-900 leading-none" x-text="audit.formatted_date"></div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">User Account</div>
                                <div class="mt-1 text-sm font-bold text-gray-900 leading-none" x-text="audit.user_name || 'System / Guest'"></div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Event Type</div>
                                <div class="mt-1">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-indigo-100 text-indigo-800" x-text="audit.event"></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-matcha-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Data Comparison (Old vs New)
                            </h4>
                            <div class="overflow-hidden border border-gray-200 rounded-xl shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Field Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Old Value</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <template x-for="(values, field) in audit.diff" :key="field">
                                            <tr :class="values.old !== values.new ? 'bg-amber-50/50' : ''" class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 font-bold text-gray-900" x-text="field"></td>
                                                <td class="px-6 py-4 text-gray-600 font-mono text-xs whitespace-pre-wrap" x-text="values.old"></td>
                                                <td class="px-6 py-4 text-gray-900 font-mono text-xs whitespace-pre-wrap font-bold" x-text="values.new"></td>
                                            </tr>
                                        </template>
                                        <template x-if="Object.keys(audit.diff || {}).length === 0">
                                            <tr><td colspan="3" class="px-6 py-12 text-center text-gray-500 italic">No significant data changes recorded for this transaction.</td></tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-5 rounded-xl border border-gray-100 shadow-sm">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-200 pb-2">Technical Metadata</div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">IP Address</div>
                                        <div class="mt-1 text-sm font-medium text-gray-700" x-text="audit.ip_address || '—'"></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Request Origin URL</div>
                                        <div class="mt-1 text-sm font-medium text-gray-700 break-all leading-tight" x-text="audit.url || '—'"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">User Agent Header</div>
                                    <div class="mt-1 text-sm font-medium text-gray-700 leading-relaxed break-words" x-text="audit.user_agent || '—'"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-100">
                    <button @click="open = false" class="px-6 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition shadow-sm">
                        Close Detailed View
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

