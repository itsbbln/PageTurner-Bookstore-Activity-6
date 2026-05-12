<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'oinal.babylyn05@gmail.com';

$updated = User::where('email', $email)->update(['role' => 'admin']);

if ($updated) {
    echo "✓ User $email promoted to admin role.\n";
} else {
    echo "✗ User $email not found. Please register first.\n";
}
