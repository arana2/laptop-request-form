<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 { color: #7A003C; }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .card h3 { margin: 0 0 8px; }
        .meta {
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .meta p { margin: 4px 0; }
        a { color: #7A003C; }
        .error {
            background: #fff0f0;
            border: 1px solid #f5c6c6;
            padding: 16px;
            border-radius: 8px;
        }
        .footer {
            color: #999;
            font-size: 12px;
            margin-top: 32px;
        }
    </style>
</head>
<body>

<h2>New Hardware Request — AI Recommendations</h2>

{{-- Requester summary so EngIT knows who submitted and what they need --}}
<div class="meta">
    <p><strong>Requester:</strong> {{ $submission->requester_name }} ({{ $submission->requester_email }})</p>

    {{-- Only show recipient info if this request is for someone else --}}
    @if (!$submission->is_for_self && $submission->recipient_name)
        <p><strong>For:</strong> {{ $submission->recipient_name }} ({{ $submission->recipient_email }})</p>
    @endif

    <p><strong>Request Type:</strong> {{ ucfirst($submission->request_type) }}</p>
    <p><strong>Budget:</strong> {{ $submission->budget_range }}</p>
    <p><strong>OS:</strong> {{ $submission->operating_system }}</p>
    <p><strong>Submitted:</strong> {{ $submission->created_at->format('M j, Y g:i A') }}</p>
</div>

{{-- Show error state if Gemini failed --}}
@if ($failed)
    <div class="error">
        <p><strong>AI processing failed</strong> for this submission.</p>
        <p>The form data has been saved to the database. You may want to manually review
        the request or reprocess it.</p>
        <p><strong>Submission ID:</strong> {{ $submission->id }}</p>
    </div>

{{-- Show full recommendations if Gemini succeeded --}}
@else
    <p>Here are the AI-generated recommendations for this request:</p>

    {{-- Loop through each recommended computer --}}
    @foreach ($submission->ai_response['recommendations'] ?? [] as $rec)
        <div class="card">
            <h3>{{ $rec['model'] }}</h3>
            <p>{{ $rec['reason'] }}</p>
            <a href="{{ $rec['purchase_url'] }}" target="_blank">View on retailer site →</a>

            {{-- Only show accessories if any were recommended --}}
            @if (!empty($rec['accessories']))
                <hr>
                <strong>Suggested Accessories:</strong>
                <ul>
                    @foreach ($rec['accessories'] as $acc)
                        <li>
                            <strong>{{ $acc['name'] }}</strong> — {{ $acc['reason'] }}<br>
                            <a href="{{ $acc['purchase_url'] }}" target="_blank">View →</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach

    {{-- Overall summary from Gemini --}}
    @if (!empty($submission->ai_response['summary']))
        <p><strong>Summary:</strong> {{ $submission->ai_response['summary'] }}</p>
    @endif

@endif

<p class="footer">
    Automated message from the McMaster Engineering IT hardware request system.<br>
    Submission ID: {{ $submission->id }}
</p>

</body>
</html>