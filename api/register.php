<?php
declare(strict_types=1);

require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
    exit();
}

if (strlen($password) < 8) {
    echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long."]);
    exit();
}

if ($password !== $password_confirm) {
    echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
    exit();
}

try {
    // 1. Equalize Timing: Always hash the password first so the time taken is identical whether the user exists or not.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 2. Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username already exists."]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // 3. Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration successful! You can now log in."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Something went wrong. Please try again later."]);
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Register API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "An internal server error occurred."]);
}
?>
