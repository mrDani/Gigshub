<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Ensure session is active
}

// Debugging: Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';
include '../includes/auth_functions.php';

// Get the job ID from the query parameter
$job_id = $_GET['job_id'] ?? null;

if (!$job_id) {
    header('Location: home.php?error=Job not found');
    exit();
}

// Fetch the job details
$stmt = $pdo->prepare('SELECT jobs.*, categories.name AS category_name FROM jobs 
                       LEFT JOIN categories ON jobs.category_id = categories.category_id 
                       WHERE jobs.job_id = :job_id');
$stmt->execute([':job_id' => $job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: home.php?error=Job not found');
    exit();
}

// Fetch comments for the job
$stmt = $pdo->prepare('SELECT comments.*, users.username FROM comments 
                       LEFT JOIN users ON comments.user_id = users.user_id 
                       WHERE comments.job_id = :job_id 
                       ORDER BY comments.created_at DESC');
$stmt->execute([':job_id' => $job_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize error and success messages
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_content = trim($_POST['content']);

    if (empty($comment_content)) {
        $error = 'Comment content is required.';
    } else {
        try {
            $name = isLoggedIn() ? null : trim($_POST['name']);
            $user_id = isLoggedIn() ? $_SESSION['user']['user_id'] : null;

            $stmt = $pdo->prepare('INSERT INTO comments (job_id, user_id, name, content, created_at) 
                                   VALUES (:job_id, :user_id, :name, :content, NOW())');
            $stmt->execute([
                ':job_id' => $job_id,
                ':user_id' => $user_id,
                ':name' => $name,
                ':content' => $comment_content
            ]);

            // Refresh the page after successful submission
            header("Location: job_details.php?job_id=$job_id");
            exit();
        } catch (Exception $e) {
            error_log('Error while inserting comment: ' . $e->getMessage());
            $error = 'An error occurred while submitting your comment.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><?= htmlspecialchars($job['title']) ?></h1>
            <a href="apply_job.php?job_id=<?= $job_id ?>" class="btn btn-success">Apply for Job</a>
        </div>
        <p class="text-muted">Category: <?= htmlspecialchars($job['category_name'] ?? 'Uncategorized') ?></p>
        <p class="text-muted">Created At: <?= htmlspecialchars($job['created_at']) ?></p>
        <p class="text-muted">Location: <?= htmlspecialchars($job['location'] ?? 'N/A') ?></p>
        <p class="text-muted">Salary: $<?= htmlspecialchars($job['salary'] ?? 'Negotiable') ?></p>
        <hr>
        <div><?= htmlspecialchars_decode($job['description']) ?></div>

        <hr>
        <h3>Comments</h3>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Display Comments -->
        <div class="mt-4">
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="mb-3 p-3 border rounded">
                        <strong><?= htmlspecialchars($comment['username'] ?? $comment['name'] ?? 'Anonymous') ?></strong>
                        <p><?= htmlspecialchars($comment['content']) ?></p>
                        <small class="text-muted"><?= htmlspecialchars($comment['created_at']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>
        </div>

        <!-- Comment Form -->
        <form method="POST" class="mt-4">
            <?php if (!isLoggedIn()): ?>
                <div class="mb-3">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="content" class="form-label">Your Comment</label>
                <textarea id="content" name="content" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Comment</button>
        </form>
    </div>
</body>
</html>
