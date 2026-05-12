<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;

// Check for Hell University
echo "=== Searching for 'Hell' ===\n";
$hell = Book::where('title', 'like', '%Hell%')
    ->orWhere('author', 'like', '%Hell%')
    ->get(['id', 'title', 'author', 'created_at']);

echo "Found " . $hell->count() . " books:\n";
$hell->each(function ($book) {
    echo "  - [{$book->id}] {$book->title} by {$book->author} (created: {$book->created_at})\n";
});

echo "\n=== Last 5 books (by created_at DESC) ===\n";
$latest = Book::orderBy('created_at', 'desc')->take(5)->get(['id', 'title', 'author', 'created_at']);
$latest->each(function ($book) {
    echo "  - [{$book->id}] {$book->title} by {$book->author} (created: {$book->created_at})\n";
});
