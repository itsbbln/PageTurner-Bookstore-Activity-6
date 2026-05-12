@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('header')
    <div class="flex justify-between items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}"
               class="hidden sm:inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                ← Back to dashboard
            </a>
            <h1 class="text-3xl font-bold">Order #{{ $order->id }}</h1>
        </div>
        @if(auth()->user()->isAdmin())
            <div>
                <span class="px-4 py-2 rounded text-white 
                    @if($order->status === 'pending') bg-yellow-500 
                    @elseif($order->status === 'processing') bg-blue-500 
                    @elseif($order->status === 'completed') bg-green-500 
                    @else bg-red-500 
                    @endif">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        @endif
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Order Details --}}
        <div class="md:col-span-2 space-y-6">
            {{-- Items --}}
            <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
                <h2 class="text-xl font-bold mb-4">Order Items</h2>
                
                <table class="w-full">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left py-2">Book</th>
                            <th class="text-center py-2">Qty</th>
                            <th class="text-right py-2">Price</th>
                            <th class="text-right py-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3">
                                    @if($item->book)
                                        <a href="{{ route('books.show', $item->book) }}" class="text-matcha-800 hover:text-matcha-900 font-semibold">
                                            {{ $item->book->title }}
                                        </a>
                                        <div class="text-sm text-gray-600">by {{ $item->book->author }}</div>
                                    @else
                                        <span class="text-gray-500 italic">Book removed</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">{{ $item->quantity }}</td>
                                <td class="py-3 text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-right font-semibold">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Order Info --}}
            <div class="bg-white rounded shadow p-6">
                <h2 class="text-xl font-bold mb-4">Order Information</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Date:</span>
                        <span>{{ $order->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-3 py-1 rounded text-white 
                            @if($order->status === 'pending') bg-yellow-500 
                            @elseif($order->status === 'processing') bg-blue-500 
                            @elseif($order->status === 'completed') bg-green-500 
                            @else bg-red-500 
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Contact Number:</span>
                        <span>{{ $order->contact_number }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 block mb-1">Shipping Address:</span>
                        <p class="text-gray-800 whitespace-pre-line">
                            {{ $order->shipping_address }}
                        </p>
                    </div>
                    @if($order->updated_at !== $order->created_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last Updated:</span>
                            <span>{{ $order->updated_at->format('Y-m-d H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="bg-white rounded shadow p-6 h-fit">
            <h2 class="text-xl font-bold mb-4">Order Summary</h2>
            
            <div class="space-y-2 mb-4 pb-4 border-b">
                @php
                    $subtotal = $order->orderItems->sum(fn($item) => $item->subtotal);
                @endphp
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal:</span>
                    <span>₱{{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tax:</span>
                    <span>$0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Shipping:</span>
                    <span>$0.00</span>
                </div>
            </div>

            <div class="flex justify-between text-lg font-bold mb-6">
                <span>Total:</span>
                <span>₱{{ number_format($order->total_amount, 2) }}</span>
            </div>

            <div class="space-y-2">
                <a href="{{ route('orders.invoice', $order) }}" class="block text-center bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                    Download PDF Invoice
                </a>
                <!-- if admin: return to admin/orders/index -->
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.orders.index') }}" class="block text-center bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">
                        Back to Orders
                    </a>
                @else
                    <a href="{{ route('orders.index') }}" class="block text-center bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">
                        Back to Orders
                    </a>
                    <a href="{{ route('books.index') }}" class="block text-center bg-matcha-800 text-white px-4 py-2 rounded hover:bg-matcha-900 transition">
                        Continue Shopping
                    </a>
                @endif
                <!-- @if(!auth()->user()->role === 'admin')
                    
                @endif -->
            </div>
        </div>
    </div>
@endsection
