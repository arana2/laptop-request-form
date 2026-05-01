<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;

// Responsible for handling submission-related API requests
class SubmissionController extends Controller
{
    public function store(Request $request, GeminiService $gemini)
    {
        // Collect all form input data from the HTTP request
        $data = $request->all();

        /**
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
         * Validate JSON response
         */
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'Invalid AI response',
                'raw' => $raw
                ], 500);
                }

        /**
         * Return clean structured JSON
         */
        return response()->json([
            'input' => $data,
            'recommendations' => $parsed
            ]);
            }
}
