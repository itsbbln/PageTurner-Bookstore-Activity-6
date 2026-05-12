@extends('layouts.app')

@section('title', 'Categories')

@section('header')
    <h1 class="text-2xl font-bold">Categories</h1>
@endsection

@section('content')
    <div class="bg-white p-4 rounded shadow">
        @if ($categories->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="block p-4 border rounded hover:bg-gray-50">
                        <div class="font-semibold">{{ $category->name }}</div>
                        <div class="text-sm text-gray-600">{{ $category->books_count ?? 0 }} books</div>
                        @if($category->description)
                            <div class="mt-2 text-sm text-gray-700">{{ Str::limit($category->description, 120) }}</div>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        @else
            <x-alert type="info">No categories found.</x-alert>
        @endif
    </div>
@endsection
