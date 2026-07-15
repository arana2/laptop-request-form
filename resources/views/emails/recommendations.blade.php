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
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
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
        .approved-badge {
            display: inline-block;
            background: #7A003C;
            color: white;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .macbuy-block {
            background: #f9f0f4;
            border: 1px solid #e0c0cc;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }
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
        hr { border: none; border-top: 1px solid #eee; margin: 12px 0; }
    </style>
</head>
<body>

<h2>New Hardware Request — AI Recommendations</h2>

{{-- ================================ --}}
{{-- FULL FORM SUBMISSION SUMMARY     --}}
{{-- ================================ --}}
<div class="meta">
    <p class="section-label">Requester</p>
    <p>{{ $submission->requester_name }} — <a href="mailto:{{ $submission->requester_email }}">{{ $submission->requester_email }}</a></p>

    {{-- Only show if request is on behalf of someone else --}}
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
        @foreach ($submission->usage ?? [] as $usage)
            <li>
                @if ($usage === 'standard') Standard (email, Office, Teams/Zoom)
                @elseif ($usage === 'advanced') Advanced (AutoCAD, MATLAB, Photoshop, large datasets)
                @else Other: {{ $submission->usage_other }}
                @endif
            </li>
        @endforeach
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
        @if (!empty($submission->accessories))
            {{ implode(', ', array_map(fn($a) => str_replace('_', ' ', ucfirst($a)), $submission->accessories)) }}
            @if ($submission->accessories_other) + {{ $submission->accessories_other }} @endif
        @else
            None
        @endif
    </p>

    <p class="section-label">Requested Delivery Date</p>
    <p>{{ $submission->delivery_date?->format('F j, Y') ?? 'Not specified' }}</p>

    @if ($submission->additional_info)
        <p class="section-label">Additional Notes</p>
        <p>{{ $submission->additional_info }}</p>
    @endif

    <hr>

    <p class="section-label">Submitted</p>
    <p>{{ $submission->created_at->format('M j, Y g:i A') }}</p>
    <p style="font-size:12px; color:#999;">Submission ID: {{ $submission->id }}</p>
</div>

{{-- ================================ --}}
{{-- RECOMMENDED SPECS BASELINE       --}}
{{-- ================================ --}}
@if (!$failed && !empty($submission->ai_response['recommended_specs']))
    @php $specs = $submission->ai_response['recommended_specs']; @endphp
    <div style="background:#f0f4ff; border:1px solid #c0ccee; border-radius:8px; padding:16px; margin-bottom:24px;">
        <h3 style="margin:0 0 12px; color:#7A003C;">Recommended Specs Baseline</h3>
        <p style="margin:0 0 8px; font-size:13px; color:#555;">
            Use this as a reference when sourcing hardware outside the recommendations below.
        </p>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr style="border-bottom:1px solid #ddd;">
                <td style="padding:8px 12px 8px 0; font-weight:bold; width:30%; color:#333;">Processor</td>
                <td style="padding:8px 0;">{{ $specs['processor'] ?? 'Not specified' }}</td>
            </tr>
            <tr style="border-bottom:1px solid #ddd;">
                <td style="padding:8px 12px 8px 0; font-weight:bold; color:#333;">RAM</td>
                <td style="padding:8px 0;">{{ $specs['ram'] ?? 'Not specified' }}</td>
            </tr>
            <tr style="border-bottom:1px solid #ddd;">
                <td style="padding:8px 12px 8px 0; font-weight:bold; color:#333;">Storage</td>
                <td style="padding:8px 0;">{{ $specs['storage'] ?? 'Not specified' }}</td>
            </tr>
            <tr>
                <td style="padding:8px 12px 8px 0; font-weight:bold; color:#333;">Graphics</td>
                <td style="padding:8px 0;">{{ $specs['graphics'] ?? 'Not specified' }}</td>
            </tr>
        </table>
    </div>
@endif

{{-- ================================ --}}
{{-- AI RECOMMENDATIONS               --}}
{{-- ================================ --}}

@if ($failed)
    <div class="error">
        <p><strong>AI processing failed</strong> for this submission.</p>
        <p>The form data has been saved to the database. You may want to manually
        review the request or reprocess it.</p>
    </div>
@else
    <h3>AI Recommendations</h3>
    <p>Based on the request above, here are the AI-generated recommendations:</p>

    @foreach ($submission->ai_response['recommendations'] ?? [] as $rec)
        <div class="card">

            <h3>{{ $rec['model'] }}</h3>
            <p>{{ $rec['reason'] }}</p>
            <a href="{{ $rec['purchase_url'] }}" target="_blank">View on retailer site →</a>

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

    @if (!empty($submission->ai_response['summary']))
        <p><strong>Summary:</strong> {{ $submission->ai_response['summary'] }}</p>
    @endif
@endif

<p class="footer">
    Automated message from the McMaster Engineering IT hardware request system.
</p>

</body>
</html>