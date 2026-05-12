@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

                @if (session('success'))
                    <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 border border-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                @if (empty($items))
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
                        <p class="text-gray-600 mb-6">Add some books to get started!</p>
                        <a href="{{ route('books.index') }}"
                            class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            Continue Shopping
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Cart Items -->
                        <div class="lg:col-span-2">
                            <div class="space-y-4">
                                @foreach ($items as $item)
                                    <div class="border border-gray-200 rounded-lg p-4 flex gap-4">
                                        <!-- Book Image -->
                                        <div class="flex-shrink-0">
                                            @if ($item['book']->cover_image)
                                                <img src="{{ Storage::url($item['book']->cover_image) }}"
                                                    alt="{{ $item['book']->title }}"
                                                    class="w-24 h-32 object-cover rounded-lg">
                                            @else
                                                <div
                                                    class="w-24 h-32 bg-gray-200 rounded-lg flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 6.253v13m0-13c5.5 0 10-4.745 10-10.747M12 19.25v.75" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Book Details -->
                                        <div class="flex-grow">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                <a href="{{ route('books.show', $item['book']) }}" class="hover:text-indigo-600">
                                                    {{ $item['book']->title }}
                                                </a>
                                            </h3>
                                            <p class="text-sm text-gray-600">by {{ $item['book']->author }}</p>
                                            <p class="text-sm text-gray-500 mt-1">{{ $item['book']->category->name ?? 'Uncategorized' }}</p>
                                            <p class="text-2xl font-bold text-indigo-600 mt-3">
                                                ₱{{ number_format($item['book']->price, 2) }}
                                            </p>
                                        </div>

                                        <!-- Quantity & Actions -->
                                        <div class="flex flex-col items-end gap-4">
                                            <form method="POST" action="{{ route('cart.update', $item['book']) }}"
                                                class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}"
                                                    min="1" max="{{ $item['book']->stock_quantity }}"
                                                    class="w-16 px-2 py-1 border border-gray-300 rounded-lg text-center">
                                                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-700">
                                                    Update
                                                </button>
                                            </form>

                                            <div class="text-lg font-semibold text-gray-900">
                                                ₱{{ number_format($item['subtotal'], 2) }}
                                            </div>

                                            <form method="POST" action="{{ route('cart.remove', $item['book']) }}"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-sm text-red-600 hover:text-red-700 font-semibold">
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Clear Cart Button -->
                            <div class="mt-6">
                                <form method="POST" action="{{ route('cart.clear') }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-sm text-gray-600 hover:text-gray-700 underline">
                                        Clear Cart
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Checkout Details -->
                        <div class="lg:col-span-1">
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h2 class="text-xl font-bold text-gray-900 mb-4">Checkout Details</h2>

                                @auth
                                    <form method="POST" action="{{ route('orders.store') }}" class="space-y-4">
                                        @csrf

                                        {{-- Contact & Address (shown first) --}}
                                        <div>
                                            <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">
                                                Contact Number
                                            </label>
                                            <input
                                                type="text"
                                                id="contact_number"
                                                name="contact_number"
                                                value="{{ old('contact_number') }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                                required
                                            >
                                        </div>

                                        <div>
                                            <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">
                                                Shipping Address
                                            </label>
                                            <textarea
                                                id="shipping_address"
                                                name="shipping_address"
                                                rows="3"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                                required
                                            >{{ old('shipping_address') }}</textarea>
                                        </div>

                                        {{-- Order Summary (after contact & address) --}}
                                        <div class="space-y-3 mb-2 pt-2 border-t border-gray-200">
                                            <div class="flex justify-between text-gray-600">
                                                <span>Subtotal</span>
                                                <span>₱{{ number_format($total, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between text-gray-600">
                                                <span>Shipping</span>
                                                <span class="text-green-600 font-semibold">Free</span>
                                            </div>
                                            <div class="border-t border-gray-200 pt-3 flex justify-between text-lg font-bold text-gray-900">
                                                <span>Total</span>
                                                <span>₱{{ number_format($total, 2) }}</span>
                                            </div>
                                        </div>

                                        <button type="submit"
                                            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                            Checkout
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="block w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition text-center">
                                        Sign In to Checkout
                                    </a>
                                @endauth

                                <a href="{{ route('books.index') }}"
                                    class="block w-full text-center text-indigo-600 py-2 mt-2 border border-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 transition">
                                    Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
