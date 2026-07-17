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
        h3 { color: #7A003C; margin: 0 0 8px; }
        .meta {
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .meta p { margin: 4px 0; }
        .meta ul { margin: 4px 0; padding-left: 20px; }
        .section-label {
            font-weight: bold;
            color: #555;
            margin-top: 10px;
            margin-bottom: 2px;
        }
        a { color: #7A003C; }
        .footer {
            color: #999;
            font-size: 12px;
            margin-top: 32px;
        }
        hr { border: none; border-top: 1px solid #eee; margin: 12px 0; }
    </style>
</head>
<body>

<h2>Request Received</h2>

<p>Hi {{ $submission->requester_name }},</p>

<p>
    Thanks for submitting a computer hardware request. We've received it and it's
    currently being processed. You'll receive a follow-up email with recommendations shortly.
</p>

{{-- ================================ --}}
{{-- FULL FORM SUBMISSION SUMMARY     --}}
{{-- ================================ --}}
<div class="meta">
    <p class="section-label">Requester</p>
    <p>{{ $submission->requester_name }} — <a href="mailto:{{ $submission->requester_email }}">{{ $submission->requester_email }}</a></p>

    @if (!$submission->is_for_self && $submission->recipient_name)
        <p class="section-label">Requested For</p>
        <p>{{ $submission->recipient_name }} — <a href="mailto:{{ $submission->recipient_email }}">{{ $submission->recipient_email }}</a></p>
    @endif

    <hr>

    <p class="section-label">Request Type</p>
    <p>{{ ucfirst($submission->request_type) }}</p>

    <p class="section-label">Budget</p>
    <p>
        @php
            $budgetLabels = [
                'under_1000' => 'Under $1,000 CAD',
                '1000_1499'  => '$1,000 – $1,499 CAD',
                '1500_1999'  => '$1,500 – $1,999 CAD',
                '2000_plus'  => 'Over $2,000 CAD',
            ];
        @endphp
        {{ $budgetLabels[$submission->budget_range] ?? $submission->budget_range }}
    </p>

    <p class="section-label">Usage</p>
    <ul>
        <li>
            @if ($submission->usage_type === 'standard')
                Standard (email, Office, Teams/Zoom)
            @elseif ($submission->usage_type === 'advanced')
                Advanced (AutoCAD, MATLAB, Photoshop, large datasets)
            @endif
        </li>

        @if ($submission->usage_other)
            <li>Other requirements: {{ $submission->usage_other }}</li>
        @endif
    </ul>

    <p class="section-label">Portability Preference</p>
    <p>
        @php
            $portabilityLabels = [
                'lightweight'   => 'Lightweight — prioritize low weight and battery life',
                'performance'   => 'Performance over portability',
                'no_preference' => 'No preference',
            ];
        @endphp
        {{ $portabilityLabels[$submission->portability] ?? 'Not specified' }}
    </p>

    <p class="section-label">Brand Preference</p>
    <p>
        @if (!empty($submission->brands))
            {{ implode(', ', array_map('ucfirst', $submission->brands)) }}
            @if ($submission->brand_other) + {{ $submission->brand_other }} @endif
        @else
            No preference
        @endif
    </p>

    <p class="section-label">Accessories Requested</p>
    <p>
        @php
            $accessoryParts = [];
            if (!empty($submission->accessories)) {
                $accessoryParts[] = implode(', ', array_map(fn($a) => str_replace('_', ' ', ucfirst($a)), $submission->accessories));
            }
            if (!empty($submission->accessories_other)) {
                $accessoryParts[] = $submission->accessories_other;
            }
        @endphp
        {{ !empty($accessoryParts) ? implode(', ', $accessoryParts) : 'None' }}
    </p>

    <p class="section-label">Requested Delivery Date</p>
    <p>{{ $submission->delivery_date?->format('F j, Y') ?? 'Not specified' }}</p>

    @if ($submission->additional_info)
        <p class="section-label">Additional Notes</p>
        <p>{{ $submission->additional_info }}</p>
    @endif

    <hr>

    <p class="section-label">Submitted</p>
    <p>{{ $submission->created_at->setTimezone('America/Toronto')->format('M j, Y g:i A') }}</p>
    <p style="font-size:12px; color:#999;">Submission ID: {{ $submission->id }}</p>
</div>

<p class="footer">
    Automated message from the McMaster Engineering IT hardware request system.
</p>

</body>
</html>