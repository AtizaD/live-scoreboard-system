<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please login.']);
        exit();
    }
}

function requireLoginPage() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit();
    }
}

function getAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

function setAdminSession($adminId, $username) {
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['username'] = $username;
}

function logout() {
    session_destroy();
    session_start();
}
?>
