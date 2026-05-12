@extends('layouts.app')

@section('title', 'PageTurner - Online Bookstore')

@section('content')

    {{-- Hero Section --}}
    <div class="bg-matcha-800 text-white rounded-lg p-8 mb-8">
        <h1 class="text-4xl font-bold mb-4">
            Welcome to PageTurner
        </h1>

        <p class="text-xl text-matcha-100 mb-6">
            Discover your next favorite book from our extensive collection.
        </p>

        <a href="{{ route('books.index') }}"
           class="bg-matcha-300 text-matcha-900 px-6 py-3 rounded-lg font-semibold hover:bg-matcha-400 transition">
            Browse Books
        </a>
    </div>

    {{-- Categories Section --}}
    <section class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">
                Browse by Category
            </h2>
            <a href="{{ route('categories.index') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold underline">
                See All Categories →
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($categories->take(5) as $category)
                <a href="{{ route('categories.show', $category) }}"
                   class="bg-white p-4 rounded-lg shadow hover:shadow-md transition text-center">
                    
                    <h3 class="font-semibold text-gray-800">
                        {{ $category->name }}
                    </h3>

                    <p class="text-sm text-gray-500">
                        {{ $category->books_count }} books
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Featured Books Section --}}
    <section>
        <h2 class="text-2xl font-bold mb-6">
            Featured Books
        </h2>

        @forelse ($featuredBooks as $book)
            
            @if ($loop->first)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @endif

            <x-book-card :book="$book" />

            @if ($loop->last)
                </div>
            @endif

        @empty
            <x-alert type="info">
                No books available at the moment. Check back soon!
            </x-alert>
        @endforelse
    </section>

@endsection
