<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinancialReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private readonly ?string $dateFrom = null,
        private readonly ?string $dateTo = null,
        private readonly float $taxRate = 0.12,
    ) {
    }

    public function headings(): array
    {
        return ['Period', 'Total Orders', 'Completed Revenue', 'Tax Rate', 'Estimated Tax', 'Net Revenue'];
    }

    public function collection(): Collection
    {
        $q = Order::query();
        if ($this->dateFrom) {
            $q->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $q->whereDate('created_at', '<=', $this->dateTo);
        }

        $orders = $q->get(['status', 'total_amount']);
        $totalOrders = $orders->count();
        $revenue = (float) $orders->where('status', 'completed')->sum('total_amount');
        $tax = $revenue * $this->taxRate;
        $net = $revenue - $tax;

        $label = trim(($this->dateFrom ?: 'Beginning') . ' to ' . ($this->dateTo ?: 'Now'));

        return collect([[
            $label,
            $totalOrders,
            number_format($revenue, 2, '.', ''),
            ($this->taxRate * 100) . '%',
            number_format($tax, 2, '.', ''),
            number_format($net, 2, '.', ''),
        ]]);
    }
}

