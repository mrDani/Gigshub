<?php
// Initialize session and include shared utilities
session_start();

// Authentication check
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['is_admin'];
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Redirect unauthenticated users
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}
?>
