@extends('layouts.app')

@section('title', 'User Management - PageTurner')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">User Management</h1>
            <p class="text-sm text-matcha-100">Manage bookstore users, roles, and security audit</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-matcha-200 hover:text-white">← Back to Dashboard</a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        {{-- Import Section --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Import Users</h2>
                    <p class="text-sm text-gray-500 mt-1">Bulk create accounts using XLSX/CSV templates.</p>
                </div>
                <a href="{{ route('admin.users.template') }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">
                    Download Template
                </a>
            </div>

            <form class="space-y-6" method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Upload File</label>
                    <input type="file" name="file" required class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-matcha-500 focus:ring-matcha-500" />
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Default User Role</label>
                    <select name="default_role" class="block w-full rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm">
                        <option value="customer">Customer</option>
                        <option value="premium">Premium User</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-matcha-800 text-white font-bold text-sm uppercase tracking-wider hover:bg-matcha-900 transition shadow-sm">
                        Start Bulk Import
                    </button>
                    <p class="text-xs text-gray-500 italic">Processing usually takes a few seconds.</p>
                </div>
            </form>

            <div class="mt-10 pt-8 border-t border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Recent User Imports</h3>
                <div class="space-y-3">
                    @forelse ($recentUserImports as $log)
                        <div class="block rounded-xl border border-gray-200 p-4 hover:bg-matcha-50 hover:border-matcha-200 transition group">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate group-hover:text-matcha-900">{{ $log->original_filename }}</div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-bold uppercase tracking-wide
                                            @if($log->status === 'completed') bg-green-100 text-green-800
                                            @elseif($log->status === 'failed') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif
                                        ">
                                            {{ $log->status }}
                                        </span>
                                        <span class="text-gray-300">•</span>
                                        <span class="font-medium">{{ $log->created_at?->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <p class="text-sm text-gray-500 italic">No user imports found.</p>
                        </div>
                    @endforelse
                </div>
                @if($recentUserImports->hasPages())
                    <div class="mt-4">
                        {{ $recentUserImports->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Export Section --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900">Export User Data</h2>
                <p class="text-sm text-gray-500 mt-1">GDPR-compliant user data exports with role filtering.</p>
            </div>

            <form class="space-y-6" method="POST" action="{{ route('admin.users.export') }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Export Format</label>
                        <select name="format" class="block w-full rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm">
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF Document (.pdf)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Filter by Role</label>
                        <select name="role" class="block w-full rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm">
                            <option value="">All Users</option>
                            <option value="admin">Administrators</option>
                            <option value="customer">Customers</option>
                            <option value="premium">Premium Users</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer bg-white p-3 rounded-lg border border-gray-200 shadow-sm hover:bg-red-50 transition">
                            <input type="checkbox" name="redact_pii" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                            <span>
                                <span class="font-bold text-gray-900">Redact Sensitive PII (GDPR)</span>
                                <span class="block text-xs text-gray-500">Removes email and addresses from export files.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-indigo-600 text-white font-bold text-sm uppercase tracking-wider hover:bg-indigo-700 transition shadow-sm">
                        Generate Export
                    </button>
                </div>
            </form>

            <div class="mt-10 pt-8 border-t border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Export History</h3>
                <div class="space-y-3">
                    @forelse ($recentUserExports as $log)
                        <div class="block rounded-xl border border-gray-200 p-4 hover:bg-matcha-50 hover:border-matcha-200 transition group">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate group-hover:text-matcha-900">User Export ({{ strtoupper($log->format) }})</div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-bold uppercase tracking-wide bg-green-100 text-green-800">
                                            {{ $log->status }}
                                        </span>
                                        <span class="text-gray-300">•</span>
                                        <span class="font-medium">{{ $log->created_at?->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <p class="text-sm text-gray-500 italic">No user exports found.</p>
                        </div>
                    @endforelse
                </div>
                @if($recentUserExports->hasPages())
                    <div class="mt-4">
                        {{ $recentUserExports->links() }}
                    </div>
                @endif
            </div>

            <div class="mt-10 pt-8 border-t border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Order Exports</h3>
                <div class="space-y-3">
                    @forelse ($recentOrderExports as $log)
                        <div class="block rounded-xl border border-gray-200 p-4 hover:bg-matcha-50 hover:border-matcha-200 transition group">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate group-hover:text-matcha-900">
                                        {{ ucfirst($log->type) }} Export
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-bold uppercase tracking-wide bg-indigo-100 text-indigo-800">
                                            {{ $log->status }}
                                        </span>
                                        <span class="text-gray-300">•</span>
                                        <span class="font-medium">{{ $log->created_at?->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <p class="text-sm text-gray-500 italic">No order exports found.</p>
                        </div>
                    @endforelse
                </div>
                @if($recentOrderExports->hasPages())
                    <div class="mt-4">
                        {{ $recentOrderExports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- User List Table --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-900">Active Bookstore Users</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User Information</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Security Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest
                                    @if($user->role === 'admin') bg-purple-100 text-purple-800
                                    @elseif($user->role === 'premium') bg-amber-100 text-amber-800
                                    @else bg-blue-100 text-blue-800 @endif
                                ">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Orders</span>
                                        <span class="font-bold">{{ $user->orders_count }}</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Reviews</span>
                                        <span class="font-bold">{{ $user->reviews_count }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full @if($user->hasVerifiedEmail()) bg-green-500 @else bg-red-500 @endif"></span>
                                        <span class="text-xs font-medium @if($user->hasVerifiedEmail()) text-green-700 @else text-red-700 @endif">
                                            {{ $user->hasVerifiedEmail() ? 'Email Verified' : 'Unverified' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full @if($user->two_factor_enabled ?? false) bg-green-500 @else bg-gray-300 @endif"></span>
                                        <span class="text-xs font-medium @if($user->two_factor_enabled ?? false) text-green-700 @else text-gray-500 @endif">
                                            2FA: {{ ($user->two_factor_enabled ?? false) ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
