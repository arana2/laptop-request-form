<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Jobs\ProcessAIRecommendations;
use App\Models\FormSubmission;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    /**
     * Type-hinting StoreSubmissionRequest here means Laravel automatically
     * runs all the validation rules BEFORE this method body even executes.
     * If validation fails, the method never runs — Laravel returns a 422
     * JSON response with the errors automatically.
     */
    public function store(StoreSubmissionRequest $request)
    {
        // validated() returns ONLY the fields listed in rules() above.
        // Anything extra/unexpected sent in the request is silently dropped,
        // which protects against unexpected or malicious extra fields.
        $data = $request->validated();

        $submission = FormSubmission::create([
            'id'                => (string) Str::uuid(),
            'requester_name'    => $data['requester_name'],
            'requester_email'   => $data['requester_email'],
            'is_for_self'       => $data['request_for'] === 'self',
            'recipient_name'    => $data['recipient_name'] ?? null,
            'recipient_email'   => $data['recipient_email'] ?? null,
            'request_type'      => $data['request_type'],
            'budget_range'      => $data['budget_range'],
            'usage'             => array_values($data['usage'] ?? []),
            'brands'            => array_values($data['brands'] ?? []),
            'accessories'       => array_values($data['accessories'] ?? []),
            'usage_other'       => $data['other_usage'] ?? null,
            'brand_other'       => $data['brand_other'] ?? null,
            'accessories_other' => $data['accessories_other'] ?? null,
            'operating_system'  => $data['operating_system'],
            'os_other'          => $data['os_other'] ?? null,
            'delivery_date'     => $data['delivery_date'] ?? null,
            'additional_info'   => $data['additional_info'] ?? null,
            'ai_response'       => null,
            'status'            => 'pending'
        ]);

        ProcessAIRecommendations::dispatch($submission, $data);

        return response()->json([
            'message'       => 'Submission received. You will receive an email with recommendations shortly.',
            'submission_id' => $submission->id
        ]);
    }
}