<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuditArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:archive {--months=12 : Archive audits older than N months}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive audit logs older than retention window to storage and delete them';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $months = (int) $this->option('months');
        $cutoff = now()->subMonths(max(1, $months));

        $table = config('audit.drivers.database.table', 'audits');
        $connection = config('audit.drivers.database.connection', config('database.default'));

        $chunk = 2000;
        $archived = 0;
        $file = "audit/archive/{$cutoff->format('Y-m-d')}_and_older.jsonl";

        // Append JSON lines so we can stream archives
        DB::connection($connection)
            ->table($table)
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById($chunk, function ($rows) use (&$archived, $file, $connection, $table) {
                $lines = '';
                $ids = [];

                foreach ($rows as $row) {
                    $lines .= json_encode($row, JSON_UNESCAPED_SLASHES) . "\n";
                    $ids[] = $row->id;
                    $archived++;
                }

                Storage::disk('local')->append($file, rtrim($lines, "\n"));

                DB::connection($connection)->table($table)->whereIn('id', $ids)->delete();
            });

        $this->info("Archived {$archived} audit row(s) older than {$months} month(s) into {$file}.");

        return self::SUCCESS;
    }
}
