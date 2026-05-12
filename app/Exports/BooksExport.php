<?php

namespace App\Exports;

use App\Models\Book;
use App\Models\ExportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterExport;
use Maatwebsite\Excel\Events\ExportFailed;
use Maatwebsite\Excel\Events\BeforeExport;

class BooksExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithEvents, ShouldQueue, ShouldAutoSize
{
    public function __construct(
        public readonly array $filters = [],
        public readonly array $columns = [],
        public readonly ?int $exportLogId = null,
    ) {
    }

    public function query()
    {
        $q = Book::query()->with('category');

        if (! empty($this->filters['category_id'])) {
            $q->where('category_id', $this->filters['category_id']);
        }
        if (isset($this->filters['min_price'])) {
            $q->where('price', '>=', (float) $this->filters['min_price']);
        }
        if (isset($this->filters['max_price'])) {
            $q->where('price', '<=', (float) $this->filters['max_price']);
        }
        if (! empty($this->filters['stock_status'])) {
            if ($this->filters['stock_status'] === 'in_stock') {
                $q->where('stock_quantity', '>', 0);
            } elseif ($this->filters['stock_status'] === 'out_of_stock') {
                $q->where('stock_quantity', '=', 0);
            } elseif ($this->filters['stock_status'] === 'low_stock') {
                $q->whereBetween('stock_quantity', [1, 5]);
            }
        }
        if (! empty($this->filters['date_from'])) {
            $q->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $q->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $q;
    }

    public function headings(): array
    {
        $cols = $this->resolvedColumns();

        return array_map(function ($col) {
            return match ($col) {
                'stock_quantity' => 'Stock',
                'created_at' => 'Created At',
                default => ucfirst(str_replace('_', ' ', $col)),
            };
        }, $cols);
    }

    public function map($book): array
    {
        $cols = $this->resolvedColumns();

        $data = [
            'isbn' => $book->isbn,
            'title' => $book->title,
            'author' => $book->author,
            'price' => $book->price,
            'stock_quantity' => $book->stock_quantity,
            'category' => optional($book->category)->name,
            'description' => $book->description,
            'created_at' => optional($book->created_at)?->toDateTimeString(),
        ];

        return Arr::only($data, $cols);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function registerEvents(): array
    {
        if (! $this->exportLogId) {
            return [];
        }

        return [
            BeforeExport::class => function () {
                ExportLog::whereKey($this->exportLogId)->update([
                    'status' => 'running',
                    'started_at' => now(),
                ]);
            },
            AfterExport::class => function () {
                ExportLog::whereKey($this->exportLogId)->update([
                    'status' => 'completed',
                    'finished_at' => now(),
                ]);
            },
            ExportFailed::class => function () {
                ExportLog::whereKey($this->exportLogId)->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                ]);
            },
        ];
    }

    private function resolvedColumns(): array
    {
        $default = ['isbn', 'title', 'author', 'price', 'stock_quantity', 'category'];
        $cols = array_values(array_filter($this->columns));
        return count($cols) > 0 ? $cols : $default;
    }
}

