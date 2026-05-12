@extends('layouts.app')

@section('title', 'Orders Management - Admin')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Order Management</h1>
            <p class="text-sm text-matcha-100">Export orders, generate financial reports, and manage statuses.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-matcha-200 hover:text-white">← Back to Dashboard</a>
    </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 p-6">

                @if (session('success'))
                    <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($orders->count() > 0)
                    <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-4">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">Order Export Module</h2>
                                <p class="text-xs text-gray-500">Tip: leave filters blank to export all.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="rounded-xl border border-gray-200 bg-white p-5">
                                <div class="text-sm font-semibold text-gray-900">Export orders</div>
                                <form method="POST" action="{{ route('admin.orders.export') }}" class="mt-4 flex flex-wrap items-end gap-4">
                            @csrf
                            <div class="w-28">
                                <label class="block text-xs font-medium text-gray-700">Format</label>
                                <select name="format" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm">
                                    <option value="xlsx">XLSX</option>
                                    <option value="csv">CSV</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                            <div class="w-40">
                                <label class="block text-xs font-medium text-gray-700">Status</label>
                                <select name="status" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm">
                                    <option value="">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="w-40">
                                <label class="block text-xs font-medium text-gray-700">From</label>
                                <input type="date" name="date_from" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div class="w-40">
                                <label class="block text-xs font-medium text-gray-700">To</label>
                                <input type="date" name="date_to" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div class="w-44">
                                <label class="block text-xs font-medium text-gray-700">Customer ID</label>
                                <input type="number" name="customer_id" placeholder="Optional" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div class="ml-auto">
                                <button type="submit" class="inline-flex items-center justify-center rounded-md px-4 py-2 bg-matcha-800 text-white font-semibold text-xs uppercase tracking-widest hover:bg-matcha-900 transition ease-in-out duration-150">
                                    Export
                                </button>
                            </div>
                        </form>
                            </div>

                            <div class="rounded-xl border border-gray-200 bg-white p-5">
                                <div class="text-sm font-semibold text-gray-900">Export financial report</div>
                                <form method="POST" action="{{ route('admin.orders.export.financial') }}" class="mt-4 flex flex-wrap items-end gap-4">
                            @csrf
                            <div class="w-28">
                                <label class="block text-xs font-medium text-gray-700">Format</label>
                                <select name="format" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm">
                                    <option value="xlsx">XLSX</option>
                                    <option value="csv">CSV</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                            <div class="w-40">
                                <label class="block text-xs font-medium text-gray-700">From</label>
                                <input type="date" name="date_from" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div class="w-40">
                                <label class="block text-xs font-medium text-gray-700">To</label>
                                <input type="date" name="date_to" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div class="w-44">
                                <label class="block text-xs font-medium text-gray-700">Tax rate</label>
                                <input type="number" step="0.01" min="0" max="1" name="tax_rate" placeholder="e.g. 0.12" class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div class="ml-auto">
                                <button type="submit" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700">
                                    Export
                                </button>
                            </div>
                        </form>
                            </div>
                        </div>
                    </div>

                    {{-- Page size selector --}}
                    <div class="flex justify-end mb-4">
                        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex items-center gap-2 text-sm">
                            <label for="per_page" class="text-gray-700">Show:</label>
                            @php
                                $perPageSelected = $perPageInput ?? request('per_page', 5);
                            @endphp
                            <select
                                id="per_page"
                                name="per_page"
                                class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                onchange="this.form.submit()"
                            >
                                <option value="5" {{ $perPageSelected == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ $perPageSelected == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ $perPageSelected == 25 ? 'selected' : '' }}>25</option>
                                <option value="all" {{ $perPageSelected === 'all' ? 'selected' : '' }}>All</option>
                            </select>
                            <span class="text-gray-500">orders per page</span>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-t border-gray-300">
                            <thead class="bg-gray-70 border-b-2 border-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                        Order ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                        Items
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($orders as $order)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            #{{ $order->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <div class="font-medium">{{ $order->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $order->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            @foreach ($order->orderItems as $item)
                                                <div class="text-xs">
                                                    {{ $item->quantity }}x {{ $item->book->title }}
                                                </div>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            ₱{{ number_format($order->total_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                                @switch($order->status)
                                                    @case('pending')
                                                        bg-yellow-100 text-yellow-800
                                                        @break
                                                    @case('processing')
                                                        bg-blue-100 text-blue-800
                                                        @break
                                                    @case('shipped')
                                                        bg-purple-100 text-purple-800
                                                        @break
                                                    @case('delivered')
                                                        bg-green-100 text-green-800
                                                        @break
                                                    @case('cancelled')
                                                        bg-red-100 text-red-800
                                                        @break
                                                @endswitch
                                            ">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $order->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-y-2">
                                            <!-- View Details -->
                                            <a href="{{ route('orders.show', $order) }}"
                                                class="text-indigo-600 hover:text-indigo-700 font-semibold">
                                                View
                                            </a>

                                            <!-- Status Update Dropdown -->
                                            <div class="flex items-center gap-2">
                                                <form method="POST" 
                                                    action="{{ route('admin.orders.update', $order) }}"
                                                    class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="flex gap-2">
                                                        <select name="status" class="text-xs px-2 py-1 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                        </select>
                                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Orders Yet</h3>
                        <p class="text-gray-600">No customer orders have been placed.</p>
                    </div>
                @endif
</div>
@endsection
