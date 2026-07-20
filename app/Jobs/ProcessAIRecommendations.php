<?php
namespace App\Jobs;

use App\Mail\RecommendationsReady;
use App\Models\FormSubmission;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProcessAIRecommendations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times to attempt the job before marking it as failed.
     * Each attempt calls GeminiService which already retries internally,
     * so keep this low.
     */
    public int $tries = 2;

    /**
     * Timeout in seconds for a single job attempt.
     * Gemini can be slow — set higher than your HTTP timeout (60s × retries + buffer)
     */
    public int $timeout = 240;

    /**
     * Backoff in seconds for retrying the job after a failure.
     * This is the delay before the next attempt.
     */
    public array $backoff = [5, 10];

    public function __construct(
        public FormSubmission $submission,
        public array $data
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GeminiService $gemini): void
    {
        // Call the GeminiService to get AI recommendations based on the form submission data
        try {
        $raw = $gemini->getRecommendations($this->data);
        $parsed = json_decode($raw, true);

        $failed = json_last_error() !== JSON_ERROR_NONE || !empty($parsed['error']);

        // Update the submission status and send an email notification based on the result
        if ($failed) {
            $this->submission->update([
                'status'      => 'failed',
                'ai_response' => $parsed ?? ['error' => true, 'message' => $raw]
            ]);

            Mail::to(config('services.engit.email'))
                ->send(new RecommendationsReady($this->submission, failed: true));

            return;
        }

        // If successful, update the submission status and send a success email notification
        $this->submission->update([
            'status'      => 'completed',
            'ai_response' => $parsed
        ]);

        // Send an email notification to the configured email address indicating that recommendations are ready
        Mail::to(config('services.engit.email'))
            ->send(new RecommendationsReady($this->submission, failed: false));

    } 
        // If an exception occurs during the process, log the error and rethrow it to allow Laravel's retry mechanism to handle it
        catch (\Throwable $e) {
        // Log it here so we know *why*, then let it propagate so Laravel's
        // retry/failed() mechanism still handles retries correctly
            Log::error('ProcessAIRecommendations failed: ' . $e->getMessage());
            throw $e;
    }
}

/**
 * Handle a job failure.
 */
public function failed(\Throwable $exception): void
    {
        // Log the failure and update the submission status to 'failed'
        Log::error('ProcessAIRecommendations permanently failed for submission ' . $this->submission->id . ': ' . $exception->getMessage());

        // Update the submission status to 'failed' in the database
        $this->submission->update(['status' => 'failed']);

        // Send an email notification to the configured email address indicating that the job has failed
        Mail::to(config('services.engit.email'))
            ->send(new RecommendationsReady($this->submission, failed: true));
    }
}