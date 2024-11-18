<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Ensure session is started
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

// function isAdmin() {
//     return isLoggedIn() && $_SESSION['user']['is_admin'];
// }
?>
