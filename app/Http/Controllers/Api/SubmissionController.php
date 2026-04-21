<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Responsible for handling submission-related API requests
class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        // Log full payload for debugging
        Log::info('Submission received', $request->all());

        return response()->json([
            'message' => 'Submission received successfully',
            'data' => $request->all()
        ]);
    }
}
