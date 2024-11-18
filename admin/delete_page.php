<?php
include '../includes/db.php';
include '../includes/auth_functions.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Restrict access to logged-in users
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Get the page ID from the POST request
$page_id = $_POST['page_id'] ?? null;

if (!$page_id) {
    header('Location: ../views/home.php?error=Invalid page ID');
    exit();
}

try {
    // Delete the page
    $stmt = $pdo->prepare('DELETE FROM pages WHERE page_id = :page_id');
    $stmt->execute([':page_id' => $page_id]);

    // Redirect to home.php with success message
    header('Location: ../views/home.php?message=Page deleted successfully');
    exit();
} catch (Exception $e) {
    // Handle potential errors
    header('Location: ../views/home.php?error=Unable to delete page');
    exit();
}
?>
