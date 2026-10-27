{{-- Status page for an Instagram data-deletion request (MetaInstagramCallbackController::deletionStatus). --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instagram data deletion - {{ config('app.name') }}</title>
    <style>
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Arial, sans-serif; background: #f5f6fa; color: #111827; }
        .card { max-width: 560px; margin: 64px auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 28px 30px; }
        h1 { font-size: 20px; margin: 0 0 12px; }
        p { font-size: 14px; line-height: 1.6; color: #374151; margin: 0 0 10px; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Instagram data deletion</h1>
        <p>Confirmation code: <code>{{ $code }}</code></p>
        @if ($record)
            <p><b>Completed</b> on {{ $record['completed_at'] }}.</p>
            <p>The Instagram connection and all data {{ config('app.name') }} had synced from it (posts, account insights and messages) have been deleted.</p>
        @else
            <p>We could not find a deletion request with this code. If you think this is a mistake, please contact us through <a href="{{ url('/contact') }}">our contact page</a>.</p>
        @endif
    </div>
</body>
</html>
