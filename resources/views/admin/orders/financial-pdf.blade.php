<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>Financial Report</h2>
    <p>Period: {{ $dateFrom ?: 'Beginning' }} to {{ $dateTo ?: 'Now' }}</p>
    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th>Total Orders</th>
                <th>Completed Revenue</th>
                <th>Tax Rate</th>
                <th>Estimated Tax</th>
                <th>Net Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

