<?php
// config/database.php
$db_host = '127.0.0.1';
$db_user = 'root'; // XAMPP default
$db_pass = ''; // XAMPP default
$db_name = 'smart_tasks_db';

// Create connection using OOP mysqli
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    // In production, log the error rather than displaying it
    error_log("Connection failed: " . $conn->connect_error);
    // Don't die here so that scripts/migrate.php can handle the absence of the DB gracefully
}
?>
