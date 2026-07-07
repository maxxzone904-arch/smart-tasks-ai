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

if (empty($username) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
    exit();
}

$stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$isValid = false;
if ($row = $result->fetch_assoc()) {
    $isValid = password_verify($password, $row['password_hash']);
} else {
    // Dummy check to mitigate timing attacks (Username Enumeration)
    password_verify($password, '$2y$10$dummyHashStringThatIs60CharsLong......................');
}

if ($isValid) {
    session_regenerate_id(true); // Prevent session fixation
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['username'] = $row['username'];
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
}
$stmt->close();
?>
