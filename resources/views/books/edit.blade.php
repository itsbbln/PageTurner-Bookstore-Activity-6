@extends('layouts.app')

@section('title', 'Edit Book - PageTurner')

@section('header')
    <h1 class="text-3xl font-bold text-gray-900">Edit Book</h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-medium mb-2">Title *</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $book->title) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                    required
                >
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Author --}}
            <div class="mb-4">
                <label for="author" class="block text-gray-700 font-medium mb-2">Author *</label>
                <input
                    type="text"
                    name="author"
                    id="author"
                    value="{{ old('author', $book->author) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('author') border-red-500 @enderror"
                    required
                >
                @error('author')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category --}}
            <div class="mb-4">
                <label for="category_id" class="block text-gray-700 font-medium mb-2">Category *</label>
                <select
                    name="category_id"
                    id="category_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('category_id') border-red-500 @enderror"
                    required
                >
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string)old('category_id', $book->category_id) === (string)$category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- ISBN & Price --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="isbn" class="block text-gray-700 font-medium mb-2">ISBN *</label>
                    <input
                        type="text"
                        name="isbn"
                        id="isbn"
                        value="{{ old('isbn', $book->isbn) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('isbn') border-red-500 @enderror"
                        required
                    >
                    @error('isbn')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="price" class="block text-gray-700 font-medium mb-2">Price (₱) *</label>
                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        id="price"
                        value="{{ old('price', $book->price) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('price') border-red-500 @enderror"
                        required
                    >
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Stock Quantity --}}
            <div class="mb-4">
                <label for="stock_quantity" class="block text-gray-700 font-medium mb-2">Stock Quantity *</label>
                <input
                    type="number"
                    name="stock_quantity"
                    id="stock_quantity"
                    value="{{ old('stock_quantity', $book->stock_quantity) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('stock_quantity') border-red-500 @enderror"
                    required
                >
                @error('stock_quantity')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea
                    name="description"
                    id="description"
                    rows="4"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                >{{ old('description', $book->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Current Cover --}}
            @if($book->cover_image)
                <div class="mb-4">
                    <p class="block text-gray-700 font-medium mb-2">Current Cover</p>
                    <img
                        src="{{ asset('storage/' . $book->cover_image) }}"
                        alt="{{ $book->title }}"
                        class="h-40 w-auto rounded border"
                    >
                </div>
            @endif

            {{-- Cover Image --}}
            <div class="mb-6">
                <label for="cover_image" class="block text-gray-700 font-medium mb-2">Replace Cover Image</label>
                <input
                    type="file"
                    name="cover_image"
                    id="cover_image"
                    accept="image/*"
                    class="w-full border-gray-300 rounded-md shadow-sm @error('cover_image') border-red-500 @enderror"
                >
                @error('cover_image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex justify-end space-x-4">
                <a href="{{ route('books.show', $book) }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
