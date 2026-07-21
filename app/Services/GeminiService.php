<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * Calls the Gemini API to get AI recommendations based on the provided data.
     * Implements retry logic for transient errors and overload conditions.
     *
     * @param array $data The form submission data to send to Gemini.
     * @param int $maxRetries Maximum number of retry attempts for transient errors.
     * @param int $delaySeconds Delay in seconds between retry attempts.
     * @return string The raw response from the Gemini API or a JSON-encoded error message.
     */
    public function getRecommendations(array $data, int $maxRetries = 3, int $delaySeconds = 3)
    {
        $apiKey = config('services.gemini.key');
        $prompt = $this->buildPrompt($data);
        $systemInstruction = $this->buildSystemInstruction();

        $attempt = 0;
        $lastError = null;

        // Retry loop for handling transient errors
        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key={$apiKey}",
                    [
                        "system_instruction" => [
                            "parts" => [
                                ["text" => $systemInstruction]
                            ]
                        ],
                        "contents" => [
                            [
                                "parts" => [
                                    ["text" => $prompt]
                                ]
                            ]
                        ]
                    ]
                );

                // If the response is successful, extract and return the text
                if ($response->successful()) {
                    $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $text = str_replace(['```json', '```'], '', $text);
                    return trim($text);
                }

                // If the response is not successful, capture the error message
                $lastError = $response->json()['error']['message'] ?? 'Unknown Gemini API error';

                // Determine if the error is retryable based on status code or specific error messages
                $retryableStatus = in_array($response->status(), [429, 500, 503]);

                // Check for specific error messages that indicate overload or high demand
                $isOverloaded = str_contains(strtolower($lastError), 'high demand')
                    || str_contains(strtolower($lastError), 'overloaded')
                    || str_contains(strtolower($lastError), 'quota exceeded')
                    || str_contains(strtolower($lastError), 'please retry');

                // If the error is not retryable and not due to overload, break the loop
                if (!$retryableStatus && !$isOverloaded) {
                    break;
                }

                // If the error is retryable, wait before the next attempt
                if ($attempt < $maxRetries) {
                    sleep($delaySeconds);
                }

            //  If the error is due to overload, log it and retry
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastError = "Connection timeout on attempt {$attempt}: " . $e->getMessage();

                if ($attempt < $maxRetries) {
                    sleep($delaySeconds);
                }
            }
        }

        // If all attempts fail, return a structured error response
        return json_encode([
            'error' => true,
            'message' => $lastError
        ]);
    }

    /**
     * Builds the system instruction that is sent to Gemini.
     * 
     * This is a static instruction that tells Gemini how to behave and what to focus on when generating recommendations.
     */
    private function buildSystemInstruction(): string
    {
        return "You are an experienced hardware procurement specialist on a university IT team, thinking like a practical systems administrator. You evaluate requests based on actual workload requirements and communicate like one professional speaking to another, not a retail salesperson.

    Ground your reasoning in specifics: multitasking headroom, sustained performance for CAD/simulation, storage I/O for large datasets, battery efficiency for portability. Avoid vague marketing language like 'powerful' without justification.

    If budget allows more than the stated usage strictly requires, a solid well-specced option within that tier is fine, as long as it stays appropriate for the actual workload and doesn't overshoot into a different usage category (no workstation-class hardware for Standard usage, even if budget allows it).

    Be direct, resourceful, and confident, while remaining approachable.";
    }

    /**
     * Builds the prompt that is sent to Gemini.
     * 
     * We take form inputs and convert them into a structured instruction for the AI.
     */
    private function buildPrompt(array $data)
    {
        // Map usage type to readable description
        $usageMap = [
            'standard' => 'Email, web browsing, Microsoft Office, Acrobat, Teams/Zoom',
            'advanced' => 'AutoCAD, MATLAB, Adobe Photoshop, working with large datasets'
        ];

        $usageText = $usageMap[$data['usage_type'] ?? ''] ?? 'Not specified';

        if (!empty($data['usage_other'])) {
            $usageText .= '; Additional requirements: ' . $data['usage_other'];
        }

        // Map budget types to detailed descriptions
        $budgetMap = [
            'under_1000' => 'Under $1,000 CAD',
            '1000_1499' => '$1,000–$1,499 CAD',
            '1500_1999' => '$1,500–$1,999 CAD',
            '2000_plus' => 'Over $2,000 CAD'
        ];

        $budgetText = $budgetMap[$data['budget_range']] ?? '';

        // Build brand text for the prompt
        $brands = $data['brands'] ?? [];
        $brandText = in_array('no_preference', $brands)
            ? 'No preference — recommend the best options regardless of brand'
            : implode(', ', $brands) . ($data['brand_other'] ? ', ' . $data['brand_other'] : '');

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

        if (!empty($data['accessories_other'])) {
            $accessoriesText .= ($accessoriesText ? ', ' : '') . $data['accessories_other'];
        }

        // Map portability preference to a readable description for the prompt
        $portabilityMap = [
            'lightweight'    => 'Lightweight and easy to carry — prioritize low weight and battery life',
            'performance'    => 'Performance over portability — heavier workstation-class machine is acceptable',
            'no_preference'  => 'No portability preference'
        ];
        $portabilityText = $portabilityMap[$data['portability'] ?? ''] ?? 'Not specified';


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
            \"is_approved_option\": false,
            \"accessories\": [
                {
                    \"name\": \"string\",
                    \"reason\": \"string\",
                    \"purchase_url\": \"string\"
                }
            ]
        }
    ],
    \"recommended_specs\": {
        \"processor\": \"string\",
        \"ram\": \"string\",
        \"storage\": \"string\",
        \"graphics\": \"string\"
    },
    \"summary\": \"string\"
}

RECOMMENDED SPECS RULES:
- recommended_specs represents the MINIMUM reasonable baseline that meets the user's stated needs, not the best possible spec, and not a comfortable buffer above what is needed
- It is NOT tied to any specific recommendation, it is a general guide for the university IT team
- Default to the most cost-effective tier that satisfies the actual workload described. Do not recommend higher-tier components to be safe. If Standard usage is selected, assume email, browsing, Office, and Teams, and recommend accordingly, even if budget allows for more
- Only scale up processor, RAM, or graphics if the Usage Details explicitly justify it, such as AutoCAD, MATLAB, large datasets, or video editing. General mentions like occasional multitasking do not justify workstation-tier specs
- processor: provide a specific processor example with generation, for example Intel Core Ultra 5 or AMD Ryzen 5 7500 for Standard usage, or Intel Core i7 or AMD Ryzen 7 for Advanced usage. Also include core count guidance (e.g., quad-core minimum, octa-core preferred). Do not use vague terms like tier or range
- ram: suggest the minimum sufficient amount with justification, for example 8 to 16 GB for standard multitasking, or 16 to 32 GB for large datasets or multiple engineering applications open at once
- storage: suggest the minimum reasonable size for the stated workload
- graphics: integrated is the default. Only recommend discrete graphics if the usage explicitly requires it, such as CAD, video editing, or machine learning. Do not recommend discrete graphics for standard office use just in case
- Be specific and practical, the university IT team will use this as a reference when sourcing hardware, and unnecessarily high specs waste budget

RECOMMENDATION RULES:
- Provide exactly 3 computer recommendations total
- Recommendations MUST be available for purchase in Canada
- Prefer Canadian retailers (Dell Canada, Lenovo Canada, Apple Canada)
- Avoid US-only URLs
- Prices must align with the user's budget in CAD
- Do not include any gaming laptops
- Each recommendation MUST include: model, reason, purchase_url, accessories array
- Accessories array must always exist (can be empty if none requested)
- If the user did not request any accessories, return empty accessories arrays for all recommendations. Do not suggest accessories unprompted.
- Brand implies OS: Dell/Lenovo/HP = Windows, Apple = macOS
- If only Apple is selected, provide 3 macOS recommendations only
- If a mix of Apple and Windows brands is selected, provide recommendations across both platforms
- Return ONLY valid JSON — no markdown, no code fences, no commentary

- Each recommendation MUST include:
  - model (string)
  - reason (string)
  - purchase_url (string)
  - accessories (array)

User request data:
- Request Type: " . ($data['request_type'] ?? '') . "
- Budget Range: " . $budgetText . "
- Usage Details: " . $usageText . "
- Brand Preference (OS is implied by brand): " . $brandText . "
- Portability Preference: " . $portabilityText . "
- Accessories Requested: " . $accessoriesText . "
";
    }
}