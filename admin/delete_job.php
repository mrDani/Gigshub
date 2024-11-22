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

// Get the job ID from the POST request
$job_id = $_POST['job_id'] ?? null;

if (!$job_id) {
    header('Location: ../views/home.php?error=Invalid job ID');
    exit();
}

try {
    // Delete the job
    $stmt = $pdo->prepare('DELETE FROM jobs WHERE job_id = :job_id');
    $stmt->execute([':job_id' => $job_id]);

    // Redirect to home.php with success message
    header('Location: ../views/home.php?message=Job deleted successfully');
    exit();
} catch (Exception $e) {
    // Handle potential errors
    header('Location: ../views/home.php?error=Unable to delete job');
    exit();
}
?>
