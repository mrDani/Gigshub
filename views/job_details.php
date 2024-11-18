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

// Get the page ID from the query parameter
$page_id = $_GET['page_id'] ?? null;

if (!$page_id) {
    header('Location: home.php?error=Page not found');
    exit();
}

// Fetch the page details
$stmt = $pdo->prepare('SELECT pages.*, categories.name AS category_name FROM pages 
                       LEFT JOIN categories ON pages.category_id = categories.category_id 
                       WHERE pages.page_id = :page_id');
$stmt->execute([':page_id' => $page_id]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    header('Location: home.php?error=Page not found');
    exit();
}

// Fetch comments
$stmt = $pdo->prepare('SELECT comments.*, users.username FROM comments 
                       LEFT JOIN users ON comments.user_id = users.user_id 
                       WHERE comments.page_id = :page_id 
                       ORDER BY comments.created_at DESC');
$stmt->execute([':page_id' => $page_id]);
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
            $name = isLoggedIn() ? null : $_POST['name'];
            $user_id = isLoggedIn() ? $_SESSION['user']['user_id'] : null;

            $stmt = $pdo->prepare('INSERT INTO comments (page_id, user_id, name, content) VALUES (:page_id, :user_id, :name, :content)');
            $stmt->execute([
                ':page_id' => $page_id,
                ':user_id' => $user_id,
                ':name' => $name,
                ':content' => $comment_content
            ]);

            $message = 'Comment submitted successfully.';
        } catch (Exception $e) {
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
    <title><?= htmlspecialchars($page['title']) ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4"><?= htmlspecialchars($page['title']) ?></h1>
        <p class="text-muted">Category: <?= htmlspecialchars($page['category_name'] ?? 'Uncategorized') ?></p>
        <p class="text-muted">Created At: <?= htmlspecialchars($page['created_at']) ?></p>
        <hr>
        <div><?= htmlspecialchars_decode($page['content']) ?></div>

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
