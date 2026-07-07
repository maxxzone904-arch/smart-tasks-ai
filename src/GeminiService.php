<?php
require_once 'AIServiceInterface.php';

class GeminiService implements AIServiceInterface {
    private string $apiKey;
    
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function extractTasks(string $text): array {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey;
        
        $prompt = "You are an assistant for a software developer. Extract actionable tasks from the following text. \n" .
                  "Return the result STRICTLY as a valid JSON array. Each object in the array must have exactly these keys:\n" .
                  " - 'title' (a concise, clear summary of the task)\n" .
                  " - 'description' (any supporting details or context)\n" .
                  " - 'priority' (must be exactly 'High', 'Medium', or 'Low' based on urgency indicated in the text).\n" .
                  "Do not include any explanation or markdown formatting in your response. Return ONLY the JSON array.\n" .
                  "If no tasks are found, return an empty array [].\n\n" .
                  "Here is the text:\n" . $text;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "responseMimeType" => "application/json"
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        // In local XAMPP environments, SSL verification often fails because cacert.pem is not configured.
        // For local development, we disable it so the API call actually goes through.
        // In production, you should set this to true and configure PHP's curl.cainfo.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error_msg);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Gemini API Error (HTTP $httpCode): " . $result);
        }

        $response = json_decode($result, true);
        
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            $raw_json = $response['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean up any potential Markdown wrapping
            $raw_json = preg_replace('/^```json\s*/i', '', $raw_json);
            $raw_json = preg_replace('/```\s*$/', '', $raw_json);
            
            $tasks = json_decode(trim($raw_json), true);
            if (is_array($tasks)) {
                return $tasks;
            }
        }
        
        return [];
    }
}
