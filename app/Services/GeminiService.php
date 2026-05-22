<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * Main function to send form data to Gemini AI and return computer recommendations.
     * * Includes a retry mechanism to handle temporary API overload errors.
     * 
     * This acts as a wrapper around the Gemini API.
     * @param array $data //Form input from the user
     * @param int $maxRetries //How many times to attempt the request before giving up
     * @param int $delaySeconds // How long to wait (in seconds) between each retry
     */
    public function getRecommendations(array $data, int $maxRetries = 3, int $delaySeconds = 3)
    {
        // Get API key from environment file (.env)
        $apiKey = env('GEMINI_API_KEY');

        // Convert the form data into a structured prompt string for Gemini
        $prompt = $this->buildPrompt($data);

        // Track which attempt we're on (starts at 0, increments before each request)        
        $attempt = 0;

        // Store the last error message in case all retries fail — we'll return it at the end
        $lastError = null;

        // Keep trying until we either succeed or exhaust all retry attempts
        while ($attempt < $maxRetries) {

            // Increment first so $attempt reflects the current try (1, 2, 3...)
            $attempt++;

            try {
                // Set a 60 second timeout — Gemini can be slow under load
                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
                [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => $prompt]
                            ]
                        ]
                    ]
                ]
            );

             // -------------------------------------------------------
            // SUCCESS PATH
            // If Gemini responded with a 2xx status, extract and clean the text
            // -------------------------------------------------------
            if ($response->successful()) {

                // Dig into the nested response structure to get the generated text
                $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';

                /**
                 * Gemini sometimes wraps its JSON response in markdown code fences:
                 *
                 *   ```json
                 *   { ... }
                 *   ```
                 *
                 * Strip those out so the result can be safely parsed as JSON downstream
                 */
                $text = str_replace(['```json', '```'], '', $text);

                // Remove any leading/trailing whitespace or newlines
                return trim($text);
            }

                    // -------------------------------------------------------
            // FAILURE PATH
            // Request failed — figure out why before deciding whether to retry
            // -------------------------------------------------------

            // Extract a human-readable error message from Gemini's error response
            // Falls back to a generic message if the structure is unexpected
            $lastError = $response->json()['error']['message'] ?? 'Unknown Gemini API error';

            /**
             * Determine if this is a retryable error.
             *
             * We only want to retry on temporary/transient failures:
             *   - 429: Too Many Requests (rate limit)
             *   - 500: Internal Server Error (Gemini-side issue)
             *   - 503: Service Unavailable (overloaded or down)
             *
             * We also check the error message text itself, since Gemini sometimes
             * returns a 200-range status but includes an overload message in the body
             */
            $retryableStatus = in_array($response->status(), [429, 500, 503]);

            $isOverloaded = str_contains(strtolower($lastError), 'high demand')
                || str_contains(strtolower($lastError), 'overloaded');

            /**
             * If it's NOT a retryable error (e.g. bad API key, malformed request),
             * there's no point waiting and trying again — it will fail every time.
             * Break out of the loop immediately and return the error below.
             */
            if (!$retryableStatus && !$isOverloaded) {
                break;
            }

            /**
             * If there are still attempts remaining, wait before trying again.
             *
             * We skip the sleep on the final attempt since we won't be retrying
             * anyway — no point making the user wait an extra 3 seconds for nothing.
             */
            if ($attempt < $maxRetries) {
                sleep($delaySeconds);
            }

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                /**
                 * Catches network-level failures such as:
                 * - cURL timeout (error 28)
                 * - DNS resolution failure
                 * - Connection refused
                 *
                 * These are always retryable — store the message and let the loop continue
                 */
                $lastError = "Connection timeout on attempt {$attempt}: " . $e->getMessage();
            }

            // Wait before retrying, skip sleep on the final attempt
            if ($attempt < $maxRetries) {
                sleep($delaySeconds);
            }

            // Send the prompt to the Gemini API
            $response = Http::post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
                [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => $prompt]
                            ]
                        ]
                    ]
                ]
            );
        }

        // -------------------------------------------------------
        // ALL RETRIES EXHAUSTED (or non-retryable error hit)
        // Return a structured error payload so the controller can
        // handle it cleanly and return a proper response to the frontend
        // -------------------------------------------------------
        return json_encode([
            'error' => true,
            'message' => $lastError
        ]);

    }

    /**
     * Builds the prompt that is sent to Gemini.
     * 
     * We take form inputs and convert them into a structured instruction for the AI.
     */
    private function buildPrompt($data)
    {
        // Map usage types to detailed descriptions
        $usageMap = [
            'standard' => 'Email, web browsing, Microsoft Office, Acrobat, Teams/Zoom',
            'advanced' => 'AutoCAD, MATLAB, Adobe Photoshop, working with large datasets',
            'other' => !empty($data['other_usage']) ? $data['other_usage'] : null
        ];

        // Convert selected usage into readable text
        $selectedUsage = [];
        foreach ($data['usage'] ?? [] as $usage) {
            if (isset($usageMap[$usage])) {
                $selectedUsage[] = $usageMap[$usage];
            }
        }

        $usageText = implode('; ', array_filter($selectedUsage));

        // Map budget types to detailed descriptions
        $budgetMap = [
            'under_1000' => 'Under $1,000 CAD',
            '1000_1499' => '$1,000–$1,499 CAD',
            '1500_1999' => '$1,500–$1,999 CAD',
            '2000_plus' => 'Over $2,000 CAD'
        ];

        $budgetText = $budgetMap[$data['budget_range']] ?? '';

        // Map accessories to detailed descriptions
        $accessoryMap = [
            'docking_station' => 'Docking station',
            'wired_keyboard' => 'Wired keyboard',
            'wireless_keyboard' => 'Wireless keyboard',
            'web_camera' => 'Web camera',
            'wired_mouse' => 'Wired mouse',
            'wireless_mouse' => 'Wireless mouse'
        ];

        $selectedAccessories = [];
        foreach ($data['accessories'] ?? [] as $acc) {
            if (isset($accessoryMap[$acc])) {
                $selectedAccessories[] = $accessoryMap[$acc];
            }
        }

        $accessoriesText = implode(', ', $selectedAccessories);        

        return "
    A user submitted a hardware request.

    Return ONLY valid JSON. Do not include any explanation, markdown, or extra text.

    Follow this exact structure:

    {
        \"recommendations\": [
            {
                \"model\": \"string\",
                \"reason\": \"string\",
                \"purchase_url\": \"string\",
                \"accessories\": [
                    {
                        \"name\": \"string\",
                        \"reason\": \"string\",
                        \"purchase_url\": \"string\"
                    }
                ]
            }
        ],
        \"summary\": \"string\"
    }

Rules:
- Provide exactly 3 computer recommendations
- Recommendations MUST be available for purchase in Canada
- Prefer Canadian retailers:
  - Manufacturer Canada websites (Dell Canada, Lenovo Canada, Apple Canada)
- Avoid US-only URLs
- Prices must align with the user's budget in CAD
- Each recommendation must include a valid Canadian purchase URL

- Each recommendation MUST include:
  - model (string)
  - reason (string)
  - purchase_url (string)
  - accessories (array)

- Accessories array must always exist (can be empty)
- If no accessories were requested, return an empty array

- Return ONLY valid JSON
- Do NOT include markdown, code fences, or commentary
- Do NOT include text before or after JSON

    User request data:
    Request Type: " . ($data['request_type'] ?? '') . "
    Budget Range: " . $budgetText . "
    Usage Details: " . $usageText . "
    Operating System: " . ($data['operating_system'] ?? '') . "
    Brand Preference: " . implode(', ', $data['brands'] ?? []) . "
    Accessories Requested: " . $accessoriesText . "
    ";
    }
}