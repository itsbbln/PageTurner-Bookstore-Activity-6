@extends('layouts.app')

@section('title', 'Create Category')

@section('header')
    <h1 class="text-2xl font-bold">Add New Category</h1>
@endsection

@section('content')
    <div class="bg-white p-6 rounded shadow max-w-2xl">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="name" class="block font-semibold mb-2">Category Name *</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}"
                    required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="e.g., Fiction, Science, Technology"
                >
                @error('name')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block font-semibold mb-2">Description</label>
                <textarea 
                    name="description" 
                    id="description"
                    rows="4"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Optionally describe this category..."
                >{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-matcha-800 text-white px-6 py-2 rounded hover:bg-matcha-900 transition">Create Category</button>
                <a href="{{ route('categories.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400 transition">Cancel</a>
            </div>
        </form>
    </div>
@endsection
