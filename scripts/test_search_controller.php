<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\BookController;
use App\Models\Category;

// Baseline: search only
$req1 = Request::create('/books', 'GET', ['search' => 'Hell University']);
$controller = new BookController();
$resp1 = $controller->index($req1);

if ($resp1 instanceof \Illuminate\View\View) {
    $data1 = $resp1->getData();
    $books1 = $data1['books'];
    echo 'Search-only count: ' . $books1->count() . PHP_EOL;
}

// With category also set (should be ignored)
$cat = Category::first();
$categoryId = $cat ? $cat->id : '';
$req2 = Request::create('/books', 'GET', ['search' => 'Hell University', 'category' => $categoryId, 'min_price' => '100', 'max_price' => '200']);
$resp2 = $controller->index($req2);

if ($resp2 instanceof \Illuminate\View\View) {
    $data2 = $resp2->getData();
    $books2 = $data2['books'];
    echo 'Search+filters count: ' . $books2->count() . PHP_EOL;
}
