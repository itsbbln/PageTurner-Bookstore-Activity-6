<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');
        $hasSearch = $request->has('search') && trim($request->search) !== '';

        // Apply text search on title/author when provided
        if ($hasSearch) {
            $search = trim($request->search);
            $search = preg_replace('/\s+/', ' ', $search);
            $lower = mb_strtolower($search, 'UTF-8');
            $query->where(function ($q) use ($lower) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$lower}%"])
                  ->orWhereRaw('LOWER(author) LIKE ?', ["%{$lower}%"]);
            });
        }

        // Category filter (works with or without search)
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Price range filters (work with or without search)
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float)$request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float)$request->max_price);
        }

        // Sort options
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                // Sort by average rating descending
                $query->leftJoin('reviews', 'books.id', '=', 'reviews.book_id')
                    ->selectRaw('books.id, books.category_id, books.title, books.author, books.isbn, books.price, books.stock_quantity, books.description, books.cover_image, books.created_at, books.updated_at, COALESCE(AVG(reviews.rating), 0) as avg_rating')
                    ->groupBy('books.id', 'books.category_id', 'books.title', 'books.author', 'books.isbn', 'books.price', 'books.stock_quantity', 'books.description', 'books.cover_image', 'books.created_at', 'books.updated_at')
                    ->orderByRaw('COALESCE(AVG(reviews.rating), 0) DESC');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $books = $query->paginate(12)->appends($request->query());
        $categories = Category::all();

        return view('books.index', compact('books', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'title'           => 'required|string|max:255',
            'author'          => 'required|string|max:255',
            'isbn'            => 'required|string|unique:books',
            'price'           => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'description'     => 'nullable|string',
            'cover_image'     => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = ImageService::storeBookCover(
                $request->file('cover_image')
            );
        }

        Book::create($validated);

        return redirect()->route('books.index')
            ->with('success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
        $book->load(['category', 'reviews.user']);

        $user = auth()->user();
        $canReview = false;

        if ($user && ! $user->isAdmin()) {
            $canReview = $user->orders()
                ->where('status', 'completed')
                ->whereHas('orderItems', function ($query) use ($book) {
                    $query->where('book_id', $book->id);
                })
                ->exists();
        }

        return view('books.show', compact('book', 'canReview'));
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'title'           => 'required|string|max:255',
            'author'          => 'required|string|max:255',
            'isbn'            => 'required|string|unique:books,isbn,' . $book->id,
            'price'           => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'description'     => 'nullable|string',
            'cover_image'     => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($book->cover_image) {
                ImageService::deleteImage($book->cover_image);
            }
            
            $validated['cover_image'] = ImageService::storeBookCover(
                $request->file('cover_image')
            );
        }

        $book->update($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        // Delete image if exists
        if ($book->cover_image) {
            ImageService::deleteImage($book->cover_image);
        }
        
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully!');
    }
}
