<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogRotate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:rotate {--days=7 : Archive logs older than N days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive and compress old logs in storage/logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays(max(1, $days));

        $logDir = storage_path('logs');
        if (! File::isDirectory($logDir)) {
            $this->info('No storage/logs directory found.');
            return self::SUCCESS;
        }

        $archiveDir = storage_path('logs/archive');
        File::ensureDirectoryExists($archiveDir);

        $archived = 0;

        foreach (File::files($logDir) as $file) {
            if ($file->getExtension() !== 'log') {
                continue;
            }

            $mtime = \Carbon\Carbon::createFromTimestamp($file->getMTime());
            if ($mtime->greaterThanOrEqualTo($cutoff)) {
                continue;
            }

            $sourcePath = $file->getRealPath();
            $targetPath = $archiveDir . DIRECTORY_SEPARATOR . $file->getFilename() . '.gz';

            $in = fopen($sourcePath, 'rb');
            $out = gzopen($targetPath, 'wb9');
            if (! $in || ! $out) {
                if ($in) {
                    fclose($in);
                }
                if ($out) {
                    gzclose($out);
                }
                continue;
            }

            while (! feof($in)) {
                gzwrite($out, fread($in, 1024 * 1024));
            }

            fclose($in);
            gzclose($out);

            File::delete($sourcePath);
            $archived++;
        }

        $this->info("Archived {$archived} log file(s).");

        return self::SUCCESS;
    }
}
