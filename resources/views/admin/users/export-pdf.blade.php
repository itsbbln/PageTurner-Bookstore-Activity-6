<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Users Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>Users Export</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $redact ? 'REDACTED' : $user->name }}</td>
                    <td>{{ $redact ? preg_replace('/^(.{2}).*(@.*)$/', '$1***$2', $user->email) : $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

