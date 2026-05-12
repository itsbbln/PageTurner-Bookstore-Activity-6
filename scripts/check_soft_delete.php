<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;
use Illuminate\Support\Facades\DB;

echo "=== Checking Book 161 for Soft Deletes ===\n";
$book = Book::find(161);
if ($book && isset($book->deleted_at)) {
    echo "Has deleted_at: " . ($book->deleted_at ?? 'null') . "\n";
} else {
    echo "No soft delete column or book not found\n";
}

echo "\n=== Direct SQL Check ===\n";
$raw = DB::select("SELECT id, title, category_id FROM books WHERE id = 161");
echo "Found in DB: " . count($raw) . "\n";
if (count($raw) > 0) {
    echo "Title: " . $raw[0]->title . "\n";
}

echo "\n=== Check Book Model Scopes ===\n";
$allBooks = Book::where('title', 'like', '%Hell%')->get();
echo "Using Book model: " . $allBooks->count() . "\n";

$directBooks = DB::select("SELECT * FROM books WHERE title LIKE ?", ['%Hell%']);
echo "Direct SQL: " . count($directBooks) . "\n";
