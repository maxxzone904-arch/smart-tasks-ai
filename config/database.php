<?php
// config/database.php

// Enable strict exception mode for mysqli (Code Review Fix #1)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $envPath = __DIR__ . '/../.env';
    
    // Explicit exception for missing environment file (Code Review Medium Fix #1)
    if (!file_exists($envPath)) {
        throw new Exception("Environment configuration file (.env) is missing.");
    }
    
    $env = parse_ini_file($envPath);
    $db_host = $env['DB_HOST'] ?? '127.0.0.1';
    $db_user = $env['DB_USER'] ?? 'root';
    $db_pass = $env['DB_PASS'] ?? '';
    $db_name = $env['DB_NAME'] ?? 'smart_tasks_db';

    // Create connection using OOP mysqli
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Explicitly set character set (Code Review Fix #3)
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Project-specific custom log file with secure permissions
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        // Code Review High Fix #1 & #2: Safe permissions (0755) & suppressed warning
        @mkdir($logDir, 0755, true); 
    }
    
    $errorMessage = sprintf("[%s] [DB_ERROR] Connection failed: %s%s", date('Y-m-d H:i:s'), $e->getMessage(), PHP_EOL);
    error_log($errorMessage, 3, $logDir . '/app-error.log');
    
    // Return a 500 Internal Server Error status
    http_response_code(500);
    
    // Render the decoupled premium error UI
    $errorTemplate = __DIR__ . '/../templates/errors/500.php';
    if (file_exists($errorTemplate)) {
        include $errorTemplate;
    } else {
        echo "Systems Currently Offline. Please try again later.";
    }
    
    exit(); // Hard exit to prevent script continuation
}
?>
