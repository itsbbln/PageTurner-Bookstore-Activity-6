<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;
use App\Models\Category;

echo "=== Checking Hell University Book ===\n";
$book = Book::find(161);

if ($book) {
    echo "Book exists: YES\n";
    echo "ID: {$book->id}\n";
    echo "Title: {$book->title}\n";
    echo "Category ID: {$book->category_id}\n";
    
    $category = Category::find($book->category_id);
    if ($category) {
        echo "Category found: YES - {$category->name}\n";
    } else {
        echo "Category found: NO (ORPHANED BOOK - Category {$book->category_id} doesn't exist)\n";
    }
} else {
    echo "Book not found\n";
}

echo "\n=== Testing Query (No Category Filter) ===\n";
$lower = 'hell university';
$results = Book::where(function ($q) use ($lower) {
    $q->whereRaw('LOWER(title) LIKE ?', ["%{$lower}%"])
      ->orWhereRaw('LOWER(author) LIKE ?', ["%{$lower}%"]);
})->get();

echo "Results found: " . $results->count() . "\n";
$results->each(function ($b) {
    echo "  - {$b->title} (Category ID: {$b->category_id})\n";
});
