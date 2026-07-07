<?php
declare(strict_types=1);

session_start();

// 1. Unset all session variables in memory
$_SESSION = [];

// 2. Destroy the session cookie on the user's browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finally, destroy the session file on the server
session_destroy();

// Redirect safely
header("Location: login");
exit();
?>
