<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BackupTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:trigger {--clean : Also run backup:clean after backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger an immediate backup (and optional cleanup)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting backup:run...');
        $exit = Artisan::call('backup:run', [], $this->output);

        if ($exit !== self::SUCCESS) {
            $this->error('backup:run failed.');
            return $exit;
        }

        if ($this->option('clean')) {
            $this->info('Starting backup:clean...');
            $exitClean = Artisan::call('backup:clean', [], $this->output);
            if ($exitClean !== self::SUCCESS) {
                $this->error('backup:clean failed.');
                return $exitClean;
            }
        }

        $this->info('Backup trigger completed.');

        return self::SUCCESS;
    }
}
