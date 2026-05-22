<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use Illuminate\Support\Str;

// Responsible for handling submission-related API requests
class SubmissionController extends Controller
{
    public function store(Request $request, GeminiService $gemini)
    {
        // Collect all form input data from the HTTP request
        $data = $request->all();

        /**
         * Step 1: Call AI Service
         * Send the user input to the Gemini service.
         * 
         * This triggers an AI request that return computer recommendations.
         * 
         * This service handles:
         * - building the prompt
         * - calling Gemini API
         * - returning the AI response
         */
        $raw = $gemini->getRecommendations($data);

        /**
         * Convert JSON string from Gemini into PHP array
         */
        $parsed = json_decode($raw, true);

        /**
         * If Gemini returns invalid JSON,
         * show the raw response so we can debug it
         */
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'Invalid JSON returned by Gemini',
                'raw_response' => $raw
            ], 500);
        }

        // Step 2: Save to DB
        $submission = FormSubmission::create([
            'id' => (string) Str::uuid(),
            'requester_name' => $data['requester_name'] ?? '',
            'requester_email' => $data['requester_email'] ?? '',
            'is_for_self' => ($data['request_for'] ?? 'self') === 'self',
            'recipient_name' => $data['recipient_name'] ?? null,
            'recipient_email' => $data['recipient_email'] ?? null,
            'request_type' => $data['request_type'],
            'budget_range' => $data['budget_range'],
            //'usage' => $data['usage'] ?? [],
            'usage' => array_values($data['usage'] ?? []),
            //'brands' => $data['brands'] ?? [],
            'brands' => array_values($data['brands'] ?? []),
            //'accessories' => $data['accessories'] ?? [],
            'accessories' => array_values($data['accessories'] ?? []),
            'usage_other' => $data['other_usage'] ?? null,
            'brand_other' => $data['brand_other'] ?? null,
            'accessories_other' => $data['accessories_other'] ?? null,
            'operating_system' => $data['operating_system'],
            'os_other' => $data['os_other'] ?? null,
            'delivery_date' => $data['delivery_date'] ?? null,
            'additional_info' => $data['additional_info'] ?? null,
            'ai_response' => $parsed,
            'status' => 'pending'
        ]);

        /**
         * Return clean structured JSON
         */
        return response()->json([
            'message' => 'Submission saved successfully',
            'submission_id' => $submission->id
        ]);
    }
}
