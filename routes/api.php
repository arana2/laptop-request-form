<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubmissionController;

Route::post('/submissions', [SubmissionController::class, 'store']);