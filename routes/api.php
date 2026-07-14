<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubmissionController;

Route::post('/submissions', [SubmissionController::class, 'store']);

// Limit to 5 submissions per hour per IP address - prevents Gemini quota abuse and spam submissions. This is a soft limit, not a hard limit.
Route::middleware('throttle:5,60')->post('/submissions', [SubmissionController::class, 'store']);