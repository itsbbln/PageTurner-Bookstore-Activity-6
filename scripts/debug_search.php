<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;

echo "=== Test 1: Simple search (no pagination, no eager load) ===\n";
$lower = 'hell university';
$results1 = Book::where(function ($q) use ($lower) {
    $q->whereRaw('LOWER(title) LIKE ?', ["%{$lower}%"])
      ->orWhereRaw('LOWER(author) LIKE ?', ["%{$lower}%"]);
})->orderBy('created_at', 'desc')->get();
echo "Found: " . $results1->count() . "\n";

echo "\n=== Test 2: With eager loading (like controller) ===\n";
$results2 = Book::with('category')
    ->where(function ($q) use ($lower) {
        $q->whereRaw('LOWER(title) LIKE ?', ["%{$lower}%"])
          ->orWhereRaw('LOWER(author) LIKE ?', ["%{$lower}%"]);
    })
    ->orderBy('created_at', 'desc')
    ->get();
echo "Found: " . $results2->count() . "\n";

echo "\n=== Test 3: With pagination (like controller) ===\n";
$results3 = Book::with('category')
    ->where(function ($q) use ($lower) {
        $q->whereRaw('LOWER(title) LIKE ?', ["%{$lower}%"])
          ->orWhereRaw('LOWER(author) LIKE ?', ["%{$lower}%"]);
    })
    ->orderBy('created_at', 'desc')
    ->paginate(12);
echo "Total: " . $results3->total() . "\n";
echo "Count on page: " . count($results3) . "\n";

echo "\n=== Test 4: Direct SQL query ===\n";
$sql = "SELECT * FROM books WHERE LOWER(title) LIKE ? OR LOWER(author) LIKE ? ORDER BY created_at DESC LIMIT 12";
$results4 = \DB::select($sql, ['%hell university%', '%hell university%']);
echo "Found: " . count($results4) . "\n";
if (count($results4) > 0) {
    echo $results4[0]->title . "\n";
}
