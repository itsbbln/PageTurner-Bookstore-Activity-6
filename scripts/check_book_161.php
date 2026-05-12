<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;

$book = Book::find(161);

echo "=== Book Record for ID 161 ===\n";
echo "ID: {$book->id}\n";
echo "Title: '{$book->title}'\n";
echo "Author: '{$book->author}'\n";
echo "Category ID: {$book->category_id}\n";
echo "Price: {$book->price}\n";
echo "Created: {$book->created_at}\n";
echo "\nFull record:\n";
echo json_encode($book->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
