<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Testing LOWER() with Multiple Spaces ===\n";

$searches = [
    'Hell University',      // 1 space
    'Hell  University',     // 2 spaces
    'Hell   University',    // 3 spaces
];

foreach ($searches as $search) {
    $lower = mb_strtolower($search, 'UTF-8');
    $pattern = "%{$lower}%";
    
    $results = DB::select(
        "SELECT id, title FROM books WHERE LOWER(title) LIKE ? OR LOWER(author) LIKE ?",
        [$pattern, $pattern]
    );
    
    echo "Search: '{$search}' (length: " . strlen($search) . ")\n";
    echo "  Pattern: '{$pattern}'\n";
    echo "  Results: " . count($results) . "\n";
}

echo "\n=== Checking What's in the Database ===\n";
$db_book = DB::select("SELECT id, title, CHAR_LENGTH(title) as title_len FROM books WHERE id = 161");
echo "DB Title: '" . $db_book[0]->title . "' (length: " . $db_book[0]->title_len . ")\n";
