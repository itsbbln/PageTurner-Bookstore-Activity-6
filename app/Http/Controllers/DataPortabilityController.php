<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DataPortabilityController extends Controller
{
    public function exportMyData(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->with('orderItems.book')->latest()->get();
        $reviews = $user->reviews()->with('book')->latest()->get();

        $payload = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => optional($user->email_verified_at)?->toJSON(),
                'created_at' => optional($user->created_at)?->toJSON(),
            ],
            'orders' => $orders->map(function ($o) {
                return [
                    'id' => $o->id,
                    'status' => $o->status,
                    'total_amount' => $o->total_amount,
                    'created_at' => optional($o->created_at)?->toJSON(),
                    'items' => $o->orderItems->map(fn ($i) => [
                        'book_id' => $i->book_id,
                        'book_title' => optional($i->book)->title,
                        'quantity' => $i->quantity,
                        'unit_price' => $i->unit_price,
                    ])->values(),
                ];
            })->values(),
            'reviews' => $reviews->map(fn ($r) => [
                'id' => $r->id,
                'book_id' => $r->book_id,
                'book_title' => optional($r->book)->title,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'created_at' => optional($r->created_at)?->toJSON(),
            ])->values(),
            'exported_at' => now()->toJSON(),
        ];

        $filename = "my_data_user_{$user->id}_" . now()->format('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function exportReadingHistory(Request $request)
    {
        $user = $request->user();
        $rows = $user->orders()
            ->where('status', 'completed')
            ->with('orderItems.book')
            ->latest()
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems->map(function ($item) use ($order) {
                    return [
                        'order_id' => $order->id,
                        'book_id' => $item->book_id,
                        'title' => optional($item->book)->title,
                        'author' => optional($item->book)->author,
                        'quantity' => $item->quantity,
                        'purchased_at' => optional($order->created_at)?->toDateTimeString(),
                    ];
                });
            })
            ->values();

        $filename = "reading_history_user_{$user->id}_" . now()->format('Ymd_His') . '.csv';
        $csv = implode(',', ['order_id', 'book_id', 'title', 'author', 'quantity', 'purchased_at']) . PHP_EOL;
        foreach ($rows as $row) {
            $csv .= implode(',', [
                $row['order_id'],
                $row['book_id'],
                '"' . str_replace('"', '""', (string) $row['title']) . '"',
                '"' . str_replace('"', '""', (string) $row['author']) . '"',
                $row['quantity'],
                $row['purchased_at'],
            ]) . PHP_EOL;
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

