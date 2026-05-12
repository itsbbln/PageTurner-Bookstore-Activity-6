<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OrderCleanupPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cleanup-pending {--hours=24 : Cancel pending orders older than N hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending orders older than a threshold and restock items';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours(max(1, $hours));

        $totalCancelled = 0;

        Order::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->with('orderItems.book')
            ->chunkById(200, function ($orders) use (&$totalCancelled) {
                foreach ($orders as $order) {
                    DB::transaction(function () use ($order, &$totalCancelled) {
                        // Restock items
                        foreach ($order->orderItems as $item) {
                            if ($item->book) {
                                $item->book->increment('stock_quantity', $item->quantity);
                            }
                        }

                        $order->update(['status' => 'cancelled']);
                        $totalCancelled++;
                    });
                }
            });

        $this->info("Cancelled {$totalCancelled} pending orders older than {$hours} hours.");

        return self::SUCCESS;
    }
}
