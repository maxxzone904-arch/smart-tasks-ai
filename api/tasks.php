<?php
declare(strict_types=1);

require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY CASE status WHEN 'In Progress' THEN 1 WHEN 'Pending' THEN 2 WHEN 'Completed' THEN 3 ELSE 4 END ASC, created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(["status" => "success", "tasks" => $tasks]);
        exit();
    }

    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $task_id = (int)($input['id'] ?? 0);

        if ($task_id <= 0) {
            echo json_encode(["status" => "error", "message" => "Invalid task ID."]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        
        echo json_encode(["status" => "success", "message" => "Task deleted."]);
        exit();
    }

    if ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $task_id = (int)($input['id'] ?? 0);
        $title = trim((string)($input['title'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $priority = (string)($input['priority'] ?? '');
        $status = (string)($input['status'] ?? '');

        if ($task_id <= 0 || empty($title)) {
            echo json_encode(["status" => "error", "message" => "Task ID and title are required."]);
            exit();
        }

        $valid_priorities = ['High', 'Medium', 'Low'];
        $valid_statuses = ['Pending', 'In Progress', 'Completed'];

        if (!in_array($priority, $valid_priorities) || !in_array($status, $valid_statuses)) {
            echo json_encode(["status" => "error", "message" => "Invalid priority or status."]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, priority = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $title, $description, $priority, $status, $task_id, $user_id);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Task updated."]);
        exit();
    }

    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed."]);

} catch (Exception $e) {
    error_log("Tasks API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "An internal server error occurred."]);
}
?>
