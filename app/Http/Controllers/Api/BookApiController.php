<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BookApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        $cursorPaginated = $query->orderBy('id')->cursorPaginate((int) $request->input('per_page', 20));

        // Lightweight ETag using latest update + query context
        $lastUpdated = (string) Book::query()->max('updated_at');
        $etag = sha1($lastUpdated . '|' . json_encode($request->query()));

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        return response()->json($cursorPaginated)->header('ETag', $etag);
    }
}

