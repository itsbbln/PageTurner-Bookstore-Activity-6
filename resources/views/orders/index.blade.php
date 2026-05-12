@extends('layouts.app')

@section('title', 'My Orders')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">My Orders</h1>
        <a href="{{ route('dashboard') }}"
           class="hidden sm:inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
            ← Back to dashboard
        </a>
    </div>
@endsection

@section('content')
    <div class="mb-4 bg-white rounded-lg shadow-md border border-gray-100 p-4">
        <div class="text-sm font-semibold text-gray-900 mb-2">Data Portability</div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('orders.export.history', ['format' => 'xlsx']) }}" class="px-3 py-2 rounded bg-indigo-600 text-white text-sm">Download XLSX</a>
            <a href="{{ route('orders.export.history', ['format' => 'csv']) }}" class="px-3 py-2 rounded bg-indigo-600 text-white text-sm">Download CSV</a>
            <a href="{{ route('orders.export.history', ['format' => 'pdf']) }}" class="px-3 py-2 rounded bg-indigo-600 text-white text-sm">Download PDF</a>
            <a href="{{ route('data.export.my') }}" class="px-3 py-2 rounded bg-emerald-600 text-white text-sm">Export My Data (JSON)</a>
            <a href="{{ route('data.export.reading-history') }}" class="px-3 py-2 rounded bg-emerald-600 text-white text-sm">Export Reading History (CSV)</a>
        </div>
    </div>

    <div class="mb-4 sm:hidden">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
            ← Back to dashboard
        </a>
    </div>
    @if ($orders->count())
        <div class="space-y-6">
            @foreach ($orders as $order)
                <div class="bg-white p-4 rounded-lg shadow-md border border-gray-100">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <strong>Order #{{ $order->id }}</strong>
                            <div class="text-sm text-gray-600">Placed: {{ $order->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">₱{{ number_format($order->total_amount, 2) }}</div>
                            <div class="text-sm text-gray-600">Status: {{ ucfirst($order->status) }}</div>
                        </div>
                    </div>

                    <div class="border-t pt-3">
                        @foreach ($order->orderItems as $item)
                            <div class="flex items-center gap-4 py-2">
                                <div class="flex-1">
                                    <div class="font-medium">{{ $item->book->title ?? 'Deleted book' }}</div>
                                    <div class="text-sm text-gray-600">Qty: {{ $item->quantity }} × {{ number_format($item->unit_price,2) }}</div>
                                </div>
                                <div class="font-semibold">{{ '₱'.number_format($item->subtotal,2) }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="pt-3 border-t mt-2">
                        <a href="{{ route('orders.invoice', $order) }}" class="text-sm text-indigo-700 hover:text-indigo-900 font-semibold">Download PDF Invoice</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @else
        <x-alert type="info">You have not placed any orders yet.</x-alert>
    @endif
@endsection
