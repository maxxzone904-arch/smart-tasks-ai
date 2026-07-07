<?php
require_once '../config/database.php';
require_once '../src/GeminiService.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$brain_dump = trim($data['brain_dump'] ?? '');

if (empty($brain_dump)) {
    http_response_code(400);
    echo json_encode(['error' => 'No text provided']);
    exit();
}

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    $apiKey = $env['GEMINI_API_KEY'] ?? '';
} else {
    $apiKey = '';
}

if (empty($apiKey) || $apiKey === 'YOUR_API_KEY_HERE') {
    http_response_code(500);
    echo json_encode(['error' => 'API Key is missing in .env file']);
    exit();
}

// 1. Initialize the AI Service (Modular Architecture)
$aiService = new GeminiService($apiKey);

// 2. Extract Tasks
try {
    $extractedTasks = $aiService->extractTasks($brain_dump);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}

if (empty($extractedTasks)) {
    echo json_encode(['success' => true, 'message' => 'No tasks found in the text.', 'tasks_added' => 0]);
    exit();
}

// 3. Save to Database
$user_id = $_SESSION['user_id'];
$insertedCount = 0;

$stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, priority, status) VALUES (?, ?, ?, ?, 'Pending')");

foreach ($extractedTasks as $task) {
    $title = mb_substr($task['title'] ?? 'Untitled Task', 0, 255);
    $description = $task['description'] ?? '';
    
    $priority = $task['priority'] ?? 'Medium';
    if (!in_array($priority, ['High', 'Medium', 'Low'])) {
        $priority = 'Medium';
    }

    $stmt->bind_param("isss", $user_id, $title, $description, $priority);
    if ($stmt->execute()) {
        $insertedCount++;
    }
}
$stmt->close();

echo json_encode([
    'success' => true,
    'message' => "Successfully extracted and added $insertedCount tasks.",
    'tasks_added' => $insertedCount
]);
