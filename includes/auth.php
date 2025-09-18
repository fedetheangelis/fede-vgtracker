<?php
session_start();

// Admin password
define('ADMIN_PASSWORD', 'nice try lol');

// Check if user is logged in as admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Login function - now only requires password
function login($password) {
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
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
            'username' => 'admin',
            'login_time' => $_SESSION['login_time'] ?? null
        ];
    }
    return [
        'is_admin' => false,
        'login_time' => null
    ];
}
?>
