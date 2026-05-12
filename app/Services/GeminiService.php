<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * Main function to send form data to Gemini AI and return computer recommendations.
     * 
     * This acts as a wrapper around the Gemini API.
     */
    public function getRecommendations(array $data)
    {
        // Get API key from environment file (.env)
        $apiKey = env('GEMINI_API_KEY');

        // Build the prompt we send to the AI model
        $prompt = $this->buildPrompt($data);

        // Make HTTP request to Gemini API
        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
            [
                "contents" => [
                    [
                        "parts" => [
                            [
                                "text" => $prompt
                            ]
                        ]
                    ]
                ]
            ]
        );

        // If the API call fails, stop and show the error
        if (!$response->successful()) {
            throw new \Exception("Gemini API error: " . $response->body());
        }

        // Extract the actual AI-generated text from the response
        $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';

        /**
         * Gemini sometimes wraps JSON responses inside markdown
         * code fences like:
         * 
         * ```json
         * { ... }
         * ```
         * 
         * Remove those wrappers so the response can be parsed properly
         */
        $text = str_replace(['```json', '```'], '', $text);

        /**
        * Remove extra whitespace/new lines
        */
        $text = trim($text);

        /**
         * Return cleaned JSON string
         */
        return $text;

        //return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
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