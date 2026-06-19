<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAIRecommendations;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use Illuminate\Support\Str;

// Responsible for handling submission-related API requests
class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        // Collect all form input data from the HTTP request
        $data = $request->all();

        // Save immediately with status 'pending' and no AI response yet
        $submission = FormSubmission::create([
            'id'              => (string) Str::uuid(),
            'requester_name'  => $data['requester_name'] ?? '',
            'requester_email' => $data['requester_email'] ?? '',
            'is_for_self'     => ($data['request_for'] ?? 'self') === 'self',
            'recipient_name'  => $data['recipient_name'] ?? null,
            'recipient_email' => $data['recipient_email'] ?? null,
            'request_type'    => $data['request_type'],
            'budget_range'    => $data['budget_range'],
            'usage'           => array_values($data['usage'] ?? []),
            'brands'          => array_values($data['brands'] ?? []),
            'accessories'     => array_values($data['accessories'] ?? []),
            'usage_other'     => $data['other_usage'] ?? null,
            'brand_other'     => $data['brand_other'] ?? null,
            'accessories_other' => $data['accessories_other'] ?? null,
            'operating_system'  => $data['operating_system'],
            'os_other'        => $data['os_other'] ?? null,
            'delivery_date'   => $data['delivery_date'] ?? null,
            'additional_info' => $data['additional_info'] ?? null,
            'ai_response'     => null,
            'status'          => 'pending'
        ]);


        // Dispatch AI processing to the background queue
        // The job receives the full submission model and original form data
        ProcessAIRecommendations::dispatch($submission, $data);

        /**
         * Return immediately - user doesn't wait for Gemini to respond. The job will handle the AI processing and email notification in the background.
         */
        return response()->json([
            'message' => 'Submission received. You will receive an email with recommendations shortly.',
            'submission_id' => $submission->id
        ]);
    }
}
