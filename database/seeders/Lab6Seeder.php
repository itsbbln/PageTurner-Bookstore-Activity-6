<?php

namespace Database\Seeders;

use App\Models\Audit;
use App\Models\Category;
use App\Models\ExportLog;
use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Lab6Seeder extends Seeder
{
    public function run(): void
    {
        // Create a sample import template + example row for screenshots/testing
        $category = Category::query()->first();
        $categoryName = $category?->name ?? 'Fiction';

        $csv = implode(',', [
            'ISBN',
            'Title',
            'Author',
            'Price',
            'Stock',
            'Category',
            'Description',
        ]) . "\n";

        $csv .= implode(',', [
            '9783161484100',
            '"Sample Book Title"',
            '"Sample Author"',
            '199.99',
            '25',
            '"' . str_replace('"', '""', $categoryName) . '"',
            '"Sample description for import demo."',
        ]) . "\n";

        Storage::disk('local')->put('imports/books/sample_import.csv', $csv);

        $admin = User::where('role', 'admin')->first();

        ImportLog::query()->create([
            'user_id' => $admin?->id,
            'type' => 'books',
            'original_filename' => 'sample_import.csv',
            'stored_path' => 'imports/books/sample_import.csv',
            'stored_disk' => 'local',
            'mime_type' => 'text/csv',
            'update_existing' => true,
            'status' => 'completed',
            'total_rows' => 1,
            'processed_rows' => 1,
            'successful_rows' => 1,
            'failed_rows' => 0,
            'started_at' => now()->subMinutes(5),
            'finished_at' => now()->subMinutes(4),
        ]);

        ExportLog::query()->create([
            'user_id' => $admin?->id,
            'type' => 'books',
            'format' => 'csv',
            'filters' => ['category_id' => $category?->id],
            'columns' => ['isbn', 'title', 'author', 'price'],
            'status' => 'completed',
            'record_count' => 0,
            'stored_disk' => 'local',
            'stored_path' => null,
            'started_at' => now()->subMinutes(3),
            'finished_at' => now()->subMinutes(2),
        ]);

        // Minimal mock audit entry for UI (checksum computed in model boot)
        Audit::query()->create([
            'user_type' => $admin ? get_class($admin) : null,
            'user_id' => $admin?->id,
            'event' => 'updated',
            'auditable_type' => 'App\\Models\\Book',
            'auditable_id' => 1,
            'old_values' => json_encode(['price' => 199.99, 'stock_quantity' => 25]),
            'new_values' => json_encode(['price' => 249.99, 'stock_quantity' => 20]),
            'url' => '/admin/books/1',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
            'tags' => 'lab6',
        ]);
    }
}

