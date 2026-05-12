<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;

echo "=== Simulating Controller Search ===\n";

// Simulate the exact search for "Hell University"
$search = "Hell University";
$lower = mb_strtolower($search, 'UTF-8');

echo "Search term: '{$search}'\n";
echo "Lowercased: '{$lower}'\n\n";

$query = Book::with('category');

// Apply the search filter exactly as in controller
$query->where(function ($q) use ($lower) {
    $q->whereRaw('LOWER(title) LIKE ?', ["%{$lower}%"])
      ->orWhereRaw('LOWER(author) LIKE ?', ["%{$lower}%"]);
});

// Sort by newest
$query->orderBy('created_at', 'desc');

// Paginate
$books = $query->paginate(12);

echo "Results from paginated query:\n";
echo "Total: {$books->total()}\n";
echo "Count on this page: " . count($books->items()) . "\n";
echo "Per page: {$books->perPage()}\n\n";

echo "Books found:\n";
foreach ($books as $book) {
    echo "  - [{$book->id}] {$book->title} by {$book->author}\n";
}
