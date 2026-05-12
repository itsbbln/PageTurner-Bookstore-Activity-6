<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .totals { margin-top: 12px; text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <div class="title">PageTurner Invoice #{{ $order->id }}</div>
    <div>Customer: {{ $order->user->name }}</div>
    <div>Email: {{ $order->user->email }}</div>
    <div>Date: {{ $order->created_at->format('Y-m-d H:i') }}</div>
    <div>Status: {{ ucfirst($order->status) }}</div>

    <table>
        <thead>
            <tr>
                <th>Book</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->book->title ?? 'Deleted book' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">Total: PHP {{ number_format($order->total_amount, 2) }}</div>
</body>
</html>

