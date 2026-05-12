@extends('layouts.app')

@section('title', 'My Dashboard - PageTurner')

@section('header')
    <h1 class="text-3xl font-bold text-white">
        Welcome, {{ $user->name }}
    </h1>
@endsection

@section('content')
    {{-- Stats Cards (same layout as admin) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Orders</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $totalOrders }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Pending Orders</h2>
            <p class="mt-3 text-3xl font-bold text-amber-600">{{ $orderStatusSummary['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Completed Orders</h2>
            <p class="mt-3 text-3xl font-bold text-green-700">{{ $orderStatusSummary['completed'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Email Verification</h2>
            <p class="mt-3 text-lg font-bold">
                @if ($user->hasVerifiedEmail())
                    <span class="text-green-700">Verified</span>
                @else
                    <span class="text-red-700">Not Verified</span>
                @endif
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Two-Factor Authentication</h2>
            <p class="mt-3 text-lg font-bold">
                @if ($user->two_factor_enabled ?? false)
                    <span class="text-green-700">Enabled</span>
                @else
                    <span class="text-gray-700">Disabled</span>
                @endif
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Reviews Submitted</h2>
            <p class="mt-3 text-3xl font-bold text-matcha-900">{{ $reviews->count() }}</p>
        </div>
    </div>

    {{-- Order Status Summary --}}
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
                <p class="text-gray-500">You have no orders yet.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($recentOrders as $order)
                        <li class="py-3 flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-900">Order #{{ $order->id }} &mdash; {{ ucfirst($order->status) }}</p>
                                <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }} &middot; ₱{{ number_format($order->total_amount, 2) }}</p>
                            </div>
                            <a href="{{ route('orders.show', $order) }}" class="text-sm text-indigo-600 hover:text-indigo-800">View</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Recently Purchased Books --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Recently Purchased Books</h2>
            @if ($recentBooks->isEmpty())
                <p class="text-gray-500">No purchased books yet.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($recentBooks as $book)
                        @if ($book)
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $book->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $book->author }}</p>
                                </div>
                                <a href="{{ route('books.show', $book) }}" class="text-sm text-matcha-800 hover:text-matcha-900 font-semibold">View Book</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Your Reviews --}}
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Your Reviews</h2>
        @if ($reviews->isEmpty())
            <p class="text-gray-500">You have not submitted any reviews yet.</p>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($reviews as $review)
                    <li class="py-3">
                        <p class="font-semibold text-gray-900">{{ $review->book->title ?? 'Unknown Book' }}</p>
                        <p class="text-sm text-yellow-500">Rating: {{ $review->rating }}/5</p>
                        @if ($review->comment)
                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($review->comment, 80) }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $review->created_at->format('M d, Y H:i') }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Quick Links --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Quick Links</h2>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('books.index') }}" class="px-4 py-2 rounded bg-matcha-800 text-white hover:bg-matcha-900 transition">Browse Books</a>
            <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-900 transition">View Order History</a>
            <a href="{{ route('data.export.my') }}" class="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700 transition">Export My Data (JSON)</a>
            <a href="{{ route('data.export.reading-history') }}" class="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700 transition">Export Reading History</a>
            <a href="{{ route('profile.edit') }}" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition">Manage Profile &amp; Security</a>
        </div>
    </div>
@endsection
