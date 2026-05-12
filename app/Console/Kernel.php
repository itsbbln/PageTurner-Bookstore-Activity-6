<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $logTask = function (string $task, string $status, ?string $message = null): void {
            try {
                DB::table('scheduled_tasks')->insert([
                    'task_name' => $task,
                    'status' => $status,
                    'message' => $message,
                    'executed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                logger()->warning('scheduled_tasks insert failed', ['task' => $task, 'error' => $e->getMessage()]);
            }
        };

        $logBackup = function (string $operation, string $status, ?string $message = null): void {
            try {
                DB::table('backup_monitoring')->insert([
                    'operation' => $operation,
                    'status' => $status,
                    'storage_disk' => 'local',
                    'message' => $message,
                    'executed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                logger()->warning('backup_monitoring insert failed', ['operation' => $operation, 'error' => $e->getMessage()]);
            }
        };

        // Backups
        $schedule->command('backup:run')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onSuccess(fn () => [logger()->info('Scheduled task success: backup:run'), $logTask('backup:run', 'success'), $logBackup('backup:run', 'success')])
            ->onFailure(fn () => [logger()->error('Scheduled task failed: backup:run'), $logTask('backup:run', 'failed'), $logBackup('backup:run', 'failed')]);

        $schedule->command('backup:clean')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->onSuccess(fn () => [logger()->info('Scheduled task success: backup:clean'), $logTask('backup:clean', 'success'), $logBackup('backup:clean', 'success')])
            ->onFailure(fn () => [logger()->error('Scheduled task failed: backup:clean'), $logTask('backup:clean', 'failed'), $logBackup('backup:clean', 'failed')]);

        // Maintenance tasks
        $schedule->command('order:cleanup-pending --hours=24')
            ->hourly()
            ->withoutOverlapping()
            ->onSuccess(fn () => [logger()->info('Scheduled task success: order:cleanup-pending'), $logTask('order:cleanup-pending', 'success')])
            ->onFailure(fn () => [logger()->error('Scheduled task failed: order:cleanup-pending'), $logTask('order:cleanup-pending', 'failed')]);

        $schedule->command('session:cleanup')
            ->daily()
            ->withoutOverlapping()
            ->onSuccess(fn () => [logger()->info('Scheduled task success: session:cleanup'), $logTask('session:cleanup', 'success')])
            ->onFailure(fn () => [logger()->error('Scheduled task failed: session:cleanup'), $logTask('session:cleanup', 'failed')]);

        $schedule->command('log:rotate --days=7')
            ->weeklyOn(0, '04:00')
            ->withoutOverlapping()
            ->onSuccess(fn () => [logger()->info('Scheduled task success: log:rotate'), $logTask('log:rotate', 'success')])
            ->onFailure(fn () => [logger()->error('Scheduled task failed: log:rotate'), $logTask('log:rotate', 'failed')]);

        $schedule->command('report:generate-daily')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->onSuccess(fn () => [logger()->info('Scheduled task success: report:generate-daily'), $logTask('report:generate-daily', 'success')])
            ->onFailure(fn () => [logger()->error('Scheduled task failed: report:generate-daily'), $logTask('report:generate-daily', 'failed')]);

        $schedule->command('notification:prune --days=90')
            ->weeklyOn(0, '05:00')
            ->withoutOverlapping()
            ->onSuccess(fn () => [logger()->info('Scheduled task success: notification:prune'), $logTask('notification:prune', 'success')])
            ->onFailure(fn () => [logger()->error('Scheduled task failed: notification:prune'), $logTask('notification:prune', 'failed')]);

        $schedule->command('audit:archive --months=12')
            ->monthlyOn(1, '01:00')
            ->withoutOverlapping()
            ->onSuccess(fn () => [logger()->info('Scheduled task success: audit:archive'), $logTask('audit:archive', 'success')])
            ->onFailure(fn () => [logger()->error('Scheduled task failed: audit:archive'), $logTask('audit:archive', 'failed')]);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
