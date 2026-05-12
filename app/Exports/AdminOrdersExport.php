<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminOrdersExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query()
    {
        $q = Order::query()->with('user');

        if (! empty($this->filters['status'])) {
            $q->where('status', $this->filters['status']);
        }
        if (! empty($this->filters['customer_id'])) {
            $q->where('user_id', (int) $this->filters['customer_id']);
        }
        if (! empty($this->filters['date_from'])) {
            $q->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $q->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $q->latest();
    }

    public function headings(): array
    {
        return ['Order ID', 'Customer Name', 'Customer Email', 'Status', 'Total Amount', 'Order Date'];
    }

    public function map($order): array
    {
        return [
            $order->id,
            optional($order->user)->name,
            optional($order->user)->email,
            $order->status,
            $order->total_amount,
            optional($order->created_at)?->toDateTimeString(),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

