<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aster Academy LMS: Error Notification {{ $errorDetails['user_id'] ? ' - User: ' . $errorDetails['user_id'] : '' }}</title>
</head>
<body>
    <h1>An error occurred in your application</h1>

    <section>
        <p><strong>Error Details:</strong></p>
        <ul>
            <li><strong>Message:</strong> {{ $errorDetails['message'] ?? 'N/A' }}</li>
            <li><strong>File:</strong> {{ $errorDetails['file'] ?? 'N/A' }}</li>
            <li><strong>Line:</strong> {{ $errorDetails['line'] ?? 'N/A' }}</li>
            <li><strong>URL:</strong> <pre>{{ $errorDetails['url'] ?? 'N/A' }}</pre></li>
            <li><strong>User ID:</strong> <pre>{{ $errorDetails['user_id'] ?? 'N/A' }}</pre></li>
        </ul>
    </section>

    <section>
        <p><strong>Additional Payload:</strong></p>
        @if(!empty($errorDetails['request_data']) && is_array($errorDetails['request_data']))
            <ul>
                @foreach ($errorDetails['request_data'] as $key => $value)
                    <li><strong>{{ $key }}:</strong> 
                        @if(is_array($value))
                            <pre>{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            {{ $value ?? 'N/A' }}
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p>No additional request data available.</p>
        @endif
    </section>
</body>
</html>