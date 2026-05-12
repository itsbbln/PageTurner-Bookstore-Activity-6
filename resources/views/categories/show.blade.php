@extends('layouts.app')

@section('title', $category->name . ' - Books')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-white-600 mt-2">{{ $category->description }}</p>
            @endif
        </div>
        @auth
            @if(auth()->user()->isAdmin())
                <div class="space-x-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="bg-matcha-700 text-white px-4 py-2 rounded hover:bg-matcha-800">Edit</a>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" onclick="return confirm('Delete this category?')">Delete</button>
                    </form>
                </div>
            @endif
        @endauth
    </div>
@endsection

@section('content')
    @if ($books->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($books as $book)
                <x-book-card :book="$book" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $books->links() }}
        </div>
    @else
        <x-alert type="info">No books in this category yet.</x-alert>
    @endif
@endsection
