<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>Audit Trail Export</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Event</th>
                <th>Model</th>
                <th>Target ID</th>
                <th>IP</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($audits as $audit)
                <tr>
                    <td>{{ $audit->id }}</td>
                    <td>{{ $audit->user_id }}</td>
                    <td>{{ $audit->event }}</td>
                    <td>{{ $audit->auditable_type }}</td>
                    <td>{{ $audit->auditable_id }}</td>
                    <td>{{ $audit->ip_address }}</td>
                    <td>{{ optional($audit->created_at)->format('Y-m-d H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

