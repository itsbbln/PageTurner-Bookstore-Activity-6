<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Book;
use Illuminate\Support\Facades\DB;

echo "=== Testing LOWER() function ===\n";

// Test 1: Direct LOWER() query
$result1 = DB::select("SELECT id, title, LOWER(title) as lower_title FROM books WHERE title = 'Hell University' LIMIT 1");
echo "Test 1 - Direct query with LOWER():\n";
echo json_encode($result1, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: LIKE with LOWER()
$result2 = DB::select("SELECT id, title FROM books WHERE LOWER(title) LIKE ? LIMIT 5", ['%hell%']);
echo "Test 2 - LIKE '%hell%' with LOWER():\n";
echo "Found " . count($result2) . " results\n";
foreach ($result2 as $row) {
    echo "  - {$row->title}\n";
}
echo "\n";

// Test 3: The exact search query the controller uses
$lower = mb_strtolower('Hell University', 'UTF-8');
$result3 = DB::select("SELECT id, title FROM books WHERE LOWER(title) LIKE ? OR LOWER(author) LIKE ?", ["%{$lower}%", "%{$lower}%"]);
echo "Test 3 - Controller query (LOWER(title) LIKE or LOWER(author) LIKE):\n";
echo "Search term (lowercased): '{$lower}'\n";
echo "Found " . count($result3) . " results\n";
foreach ($result3 as $row) {
    echo "  - {$row->title}\n";
}
