<?php
session_start();

// Admin credentials (in production, these should be in environment variables or database)
define('ADMIN_USERNAME', 'a');
define('ADMIN_PASSWORD', 'b'); // Secure password

// Check if user is logged in as admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Login function
function login($username, $password) {
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        return true;
    }
    return false;
}

// Logout function
function logout() {
    session_destroy();
    session_start();
}

// Check admin access for API endpoints
function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Admin login required.']);
        exit;
    }
}

// Get current user info
function getCurrentUser() {
    if (isAdmin()) {
        return [
            'is_admin' => true,
            'username' => $_SESSION['admin_username'] ?? 'admin',
            'login_time' => $_SESSION['login_time'] ?? null
        ];
    }
    return [
        'is_admin' => false,
        'username' => null,
        'login_time' => null
    ];
}
?>
