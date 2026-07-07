---
name: auth-security
description: "Project-specific security protocols for authentication endpoints. Enforces strict session destruction, session fixation prevention, timing attack mitigation, and safe API error handling. Use whenever building or reviewing authentication logic."
---

# Authentication Security Guidelines

This document serves as the persistent security standard for the authentication flow (Login, Registration, and Logout) in this project. You MUST follow these patterns whenever building new authentication or highly sensitive API endpoints.

## 1. Preventing Username Enumeration (Timing Attacks)
**The Threat:** If an API responds instantly when a username is incorrect, but takes 300ms when a username is correct (because it runs a slow `password_hash()` or `password_verify()` function), an attacker can measure the response time to guess which usernames exist in the database.

**The Implementation:** Always ensure the server takes the exact same amount of time regardless of whether the user exists or not.
```php
if ($row = $result->fetch_assoc()) {
    $isValid = password_verify($password, $row['password_hash']);
} else {
    // Dummy check: We run the expensive function anyway to equalize the timing
    password_verify($password, '$2y$10$dummyHashStringThatIs60CharsLong......................');
}
```

## 2. Preventing Session Fixation
**The Threat:** An attacker tricks a victim into logging in using a Session ID that the attacker already knows. Once the victim logs in, the attacker hijacks that session.

**The Implementation:** Immediately shred the old session ID and issue a cryptographically secure new one the moment a user successfully authenticates.
```php
if ($isValid) {
    session_regenerate_id(true); // Must be called BEFORE setting $_SESSION variables
    $_SESSION['user_id'] = $row['id'];
}
```

## 3. Secure Session Destruction
**The Threat:** Standard `session_destroy()` only deletes the session file on the server. The user's browser still holds the session cookie (`PHPSESSID`). If an attacker finds a way to revive that session state, the lingering cookie is a liability.

**The Implementation:** Destroy the session across all three levels (PHP Memory, User's Browser Cookie, Server Hard Drive).
```php
// 1. Clear memory
$_SESSION = [];

// 2. Clear browser cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy server file
session_destroy();
```

## 4. Preventing Information Disclosure
**The Threat:** If a database query fails, strict PHP configurations will throw a fatal Exception. Uncaught exceptions can print stack traces, exposing raw SQL queries, database paths, or server structures to the end user.

**The Implementation:** Wrap all database operations in a `try...catch` block. Log the sensitive error to a secure backend file (`logs/app-error.log`) and return a generic `500` JSON response to the frontend.
```php
try {
    $stmt->execute();
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage()); // Log securely
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "An internal server error occurred."]); // Safe output
}
```

## 5. Strict Type Declarations
**The Threat:** PHP's weak typing can silently convert strings to integers, leading to logical bypasses or mathematical errors deep in the application.

**The Implementation:** Force PHP to strictly enforce variable types in the given file.
```php
<?php
declare(strict_types=1);
```
