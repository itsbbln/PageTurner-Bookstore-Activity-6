@extends('layouts.app')

@section('title', 'All Books - PageTurner')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-white-900">
            All Books
        </h1>
        <a href="{{ route('dashboard') }}"
           class="hidden sm:inline-flex items-center text-sm text-indigo-200 hover:text-white">
            ← Back to dashboard
        </a>
    </div>
@endsection

@section('content')

    <div class="mb-4 sm:hidden">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
            ← Back to dashboard
        </a>
    </div>

    {{-- Search and Filter --}}
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form action="{{ route('books.index') }}" method="GET" id="searchForm">

            <!-- Main Search Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        type="text"
                        name="search"
                        id="searchInput"
                        value="{{ request('search') }}"
                        placeholder="Title or author..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select
                        name="category"
                        id="categorySelect"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                {{ request('category') == $category->id ? 'selected' : '' }}
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sorting -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select
                        name="sort"
                        id="sortSelect"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                    </select>
                </div>
            </div>

            <!-- Price Range Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Min Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Price (₱)</label>
                    <input
                        type="number"
                        name="min_price"
                        id="minPriceInput"
                        value="{{ request('min_price') }}"
                        placeholder="0"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <!-- Max Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Price (₱)</label>
                    <input
                        type="number"
                        name="max_price"
                        id="maxPriceInput"
                        value="{{ request('max_price') }}"
                        placeholder="Any"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <!-- Submit Button -->
                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition font-medium"
                    >
                        Filter & Search
                    </button>
                </div>
            </div>

            <!-- Clear Filters Link -->
            @if (request()->hasAny(['search', 'category', 'min_price', 'max_price', 'sort']))
                <div class="text-sm">
                    <a href="{{ route('books.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                        Clear All Filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Note: no client-side clearing logic so filters (search, category, prices, sort)
         can be combined freely and all values are submitted to the backend. --}}

    {{-- Books Grid --}}
    @if ($books->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($books as $book)
                <x-book-card :book="$book" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $books->withQueryString()->links() }}
        </div>
    @else
        <x-alert type="info">
            No books found matching your criteria.
        </x-alert>
    @endif

@endsection
