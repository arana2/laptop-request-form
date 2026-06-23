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

    public function __construct(
        public FormSubmission $submission,
        public array $data
    ) {}

    public function handle(GeminiService $gemini): void
    {
        // Call Gemini — this may take a while but runs in the background
        $raw = $gemini->getRecommendations($this->data);
        $parsed = json_decode($raw, true);

        // Check for Gemini error or bad JSON
        $failed = json_last_error() !== JSON_ERROR_NONE || !empty($parsed['error']);

        if ($failed) {
            // Mark submission as failed so you can see it in the DB
            $this->submission->update([
                'status'      => 'failed',
                'ai_response' => $parsed ?? ['error' => true, 'message' => $raw]
            ]);

            // Sends to EngIT admin only
            Mail::to(env('ENGIT_EMAIL'))
                ->send(new RecommendationsReady($this->submission, failed: true));

            return;
        }

        // Save successful AI response and mark as complete
        $this->submission->update([
            'status'      => 'completed',
            'ai_response' => $parsed
        ]);

        // Send recommendations email — failed: false tells the template to show results
        Mail::to(env('ENGIT_EMAIL'))
        ->send(new RecommendationsReady($this->submission, failed: false));
    }

/**
 * Handle a job failure.
 */
    public function failed(\Throwable $exception): void
    {
        $this->submission->update(['status' => 'failed']);

        Mail::to(env('ENGIT_EMAIL'))
            ->send(new RecommendationsReady($this->submission, failed: true));
    }
}