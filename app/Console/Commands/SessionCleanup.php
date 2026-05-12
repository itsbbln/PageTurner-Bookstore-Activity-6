<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SessionCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired sessions (database driver only)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (config('session.driver') !== 'database') {
            $this->info('Session driver is not database; nothing to prune.');
            return self::SUCCESS;
        }

        $lifetimeMinutes = (int) config('session.lifetime', 120);
        $cutoff = now()->subMinutes($lifetimeMinutes)->timestamp;

        $deleted = DB::table(config('session.table', 'sessions'))
            ->where('last_activity', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} expired sessions.");

        return self::SUCCESS;
    }
}
