<?php
require_once 'AIServiceInterface.php';

class GeminiService implements AIServiceInterface {
    private string $apiKey;
    
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function extractTasks(string $text): array {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->apiKey;
        
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

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'ignore_errors' => true // To catch HTTP errors
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            return []; // In production, throw an exception or log error
        }

        $response = json_decode($result, true);
        
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            $raw_json = $response['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean up any potential Markdown wrapping just in case (though responseMimeType should prevent this)
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
