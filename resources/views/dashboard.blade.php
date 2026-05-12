@extends('layouts.app')

@section('title', 'Admin Dashboard - PageTurner')

@section('header')
    <h1 class="text-3xl font-bold text-white">
        Admin Dashboard
    </h1>
@endsection

@section('content')
    {{-- Stats Cards (match image layout) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Books</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $totalBooks }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Categories</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $totalCategories }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Customers</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Orders</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $totalOrders }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Completed Orders</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $completedOrders }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Pending Orders</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $pendingOrders }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Backup Status</h2>
            <p class="mt-3 text-lg font-bold {{ ($latestBackup->status ?? null) === 'failed' ? 'text-red-700' : 'text-emerald-700' }}">
                {{ $latestBackup ? strtoupper($latestBackup->status) : 'N/A' }}
            </p>
            <p class="text-xs text-gray-500 mt-1">{{ $latestBackup->executed_at ?? 'No backup log yet' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Task Failures (7d)</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $recentTaskFailures }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Rate Limit Hits (24h)</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $recentRateLimitHits }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Failed Queue Jobs</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $queueFailedJobs }}</p>
        </div>
    </div>

    {{-- Order Status Summary (including Processing) --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Order Status Summary</h2>
        <div class="flex flex-wrap gap-4">
            <span class="px-4 py-2 rounded bg-amber-100 text-amber-800 font-medium">Pending: {{ $orderStatusSummary['pending'] ?? 0 }}</span>
            <span class="px-4 py-2 rounded bg-blue-100 text-blue-800 font-medium">Processing: {{ $orderStatusSummary['processing'] ?? 0 }}</span>
            <span class="px-4 py-2 rounded bg-green-100 text-green-800 font-medium">Completed: {{ $orderStatusSummary['completed'] ?? 0 }}</span>
            <span class="px-4 py-2 rounded bg-red-100 text-red-800 font-medium">Cancelled: {{ $orderStatusSummary['cancelled'] ?? 0 }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- Recent Orders --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Recent Orders</h2>
            @if ($recentOrders->isEmpty())
                <p class="text-gray-500">No orders yet.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($recentOrders->take(10) as $order)
                        <li class="py-3 flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-900">Order #{{ $order->id }} &mdash; {{ ucfirst($order->status) }}</p>
                                <p class="text-sm text-gray-500">{{ $order->user->name ?? 'N/A' }} &middot; {{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                            <a href="{{ route('admin.orders.index') }}?order={{ $order->id }}" class="text-sm text-indigo-600 hover:text-indigo-800">View</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Recent Customer Reviews --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Recent Customer Reviews</h2>
            @if ($recentReviews->isEmpty())
                <p class="text-gray-500">No reviews yet.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($recentReviews->take(10) as $review)
                        <li class="py-3">
                            <p class="font-semibold text-gray-900">{{ $review->book->title ?? 'Unknown' }}</p>
                            <p class="text-sm text-yellow-500">Rating: {{ $review->rating }}/5</p>
                            @if ($review->comment)
                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($review->comment, 80) }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">{{ $review->user->name ?? 'N/A' }} &middot; {{ $review->created_at->format('M d, Y') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Navigation Links --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Quick Links</h2>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('admin.books.create') }}" class="px-4 py-2 rounded bg-matcha-800 text-white hover:bg-matcha-900 transition">Add New Book</a>
            <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">Add New Category</a>
            <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-900 transition">View All Orders</a>
            <a href="{{ route('admin.data-management.index') }}" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition">Data Management</a>
            @if (Route::has('admin.backups.index'))
                <a href="{{ route('admin.backups.index') }}" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 transition">Backup Management</a>
            @endif
            @if (Route::has('admin.rate-limiting.index'))
                <a href="{{ route('admin.rate-limiting.index') }}" class="px-4 py-2 rounded bg-orange-600 text-white hover:bg-orange-700 transition">Rate Limiting</a>
            @endif
            @if (Route::has('admin.users.index'))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded bg-purple-600 text-white hover:bg-purple-700 transition">User Management</a>
            @endif
        </div>
    </div>
@endsection
