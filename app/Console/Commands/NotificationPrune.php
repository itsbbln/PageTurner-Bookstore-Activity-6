<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificationPrune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:prune {--days=90 : Delete notifications older than N days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old notification records past retention period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays(max(1, $days));

        $deleted = DB::table('notifications')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} notifications older than {$days} days.");

        return self::SUCCESS;
    }
}
