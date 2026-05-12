@props(['book'])

<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">

    <!-- Cover Image -->
    <div class="h-48 bg-gray-200 flex items-center justify-center">
        @if ($book->cover_image)
            <img 
                src="{{ url('storage/' . $book->cover_image) }}" 
                alt="{{ $book->title }}" 
                class="h-full w-full object-cover"
            >
        @else
            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                </path>
            </svg>
        @endif
    </div>

    <!-- Book Info -->
    <div class="p-4">
        <h3 class="font-semibold text-lg text-gray-800 truncate">
            {{ $book->title }}
        </h3>

        <p class="text-gray-600 text-sm">
            by {{ $book->author }}
        </p>

        <p class="text-matcha-800 font-bold mt-2">
            ₱{{ number_format($book->price, 2) }}
        </p>

        {{-- Stock status --}}
        <p class="mt-1 text-sm {{ $book->stock_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
            @if($book->stock_quantity > 0)
                In Stock ({{ $book->stock_quantity }})
            @else
                Out of Stock
            @endif
        </p>

        <!-- Star Rating -->
        <div class="flex items-center mt-2">
            @for ($i = 1; $i <= 5; $i++)
                @if ($i <= round($book->average_rating))
                    <!-- Filled Star -->
                    <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 
                        0l1.07 3.292a1 1 0 00.95.69h3.462c.969 
                        0 1.371 1.24.588 1.81l-2.8 
                        2.034a1 1 0 00-.364 1.118l1.07 
                        3.292c.3.921-.755 1.688-1.54 
                        1.118l-2.8-2.034a1 1 0 
                        00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 
                        0 00-.364-1.118L2.98 
                        8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 
                        0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @else
                    <!-- Empty Star -->
                    <svg class="h-4 w-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 
                        0l1.07 3.292a1 1 0 00.95.69h3.462c.969 
                        0 1.371 1.24.588 1.81l-2.8 
                        2.034a1 1 0 00-.364 1.118l1.07 
                        3.292c.3.921-.755 1.688-1.54 
                        1.118l-2.8-2.034a1 1 0 
                        00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 
                        0 00-.364-1.118L2.98 
                        8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 
                        0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @endif
            @endfor

            <span class="ml-1 text-sm text-gray-500">
                ({{ $book->reviews->count() }})
            </span>
        </div>

        <!-- Button -->
        <a href="{{ route('books.show', $book) }}" 
           class="mt-4 block text-center bg-matcha-800 text-white py-2 rounded hover:bg-matcha-900 transition">
            View Details
        </a>
    </div>
</div>
