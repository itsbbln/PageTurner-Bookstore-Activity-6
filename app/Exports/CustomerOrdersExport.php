<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerOrdersExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize
{
    public function __construct(private readonly int $userId)
    {
    }

    public function query()
    {
        return Order::query()
            ->where('user_id', $this->userId)
            ->latest();
    }

    public function headings(): array
    {
        return ['Order ID', 'Status', 'Total Amount', 'Contact Number', 'Shipping Address', 'Order Date'];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->status,
            $order->total_amount,
            $order->contact_number,
            $order->shipping_address,
            optional($order->created_at)?->toDateTimeString(),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

