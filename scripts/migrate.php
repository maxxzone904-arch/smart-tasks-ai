<?php
// scripts/migrate.php
// Note: This script should be run from CLI, not accessible via web server in production.

$db_host = '127.0.0.1';
$db_user = 'root'; // XAMPP default
$db_pass = ''; // XAMPP default
$db_name = 'smart_tasks_db';

echo "Starting Database Migration...\n";

// 1. Connect without database to create it
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

// 2. Create the DB if it doesn't exist
$sql_db = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql_db) === TRUE) {
    echo "Database created or already exists.\n";
} else {
    die("Error creating database: " . $conn->error . "\n");
}

// 3. Connect to the newly created database
$conn->select_db($db_name);

// 4. Create users table
$users_sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if ($conn->query($users_sql) === TRUE) {
    echo "Table 'users' created or already exists.\n";
} else {
    echo "Error creating table users: " . $conn->error . "\n";
}

// 5. Create tasks table
$tasks_sql = "
    CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
        status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if ($conn->query($tasks_sql) === TRUE) {
    echo "Table 'tasks' created or already exists.\n";
} else {
    echo "Error creating table tasks: " . $conn->error . "\n";
}

echo "Migration successful! You can now proceed to use the application.\n";

$conn->close();
?>
