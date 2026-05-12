<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Testing SPACE NORMALIZATION Fix ===\n";

$searches = [
    'Hell University',      // 1 space
    'Hell  University',     // 2 spaces
    'Hell   University',    // 3 spaces
];

foreach ($searches as $search) {
    $normalized = trim($search);
    $normalized = preg_replace('/\s+/', ' ', $normalized);
    $lower = mb_strtolower($normalized, 'UTF-8');
    $pattern = "%{$lower}%";
    
    $results = DB::select(
        "SELECT id, title FROM books WHERE LOWER(title) LIKE ? OR LOWER(author) LIKE ?",
        [$pattern, $pattern]
    );
    
    echo "Input: '{$search}'\n";
    echo "  Normalized: '{$normalized}'\n";
    echo "  Results: " . count($results) . "\n\n";
}
