<?php
include '../includes/db.php';        // Database connection
include '../includes/auth_functions.php'; // Authentication utilities

// Restrict access to admin users only
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch comments from the database
$stmt = $pdo->query('SELECT comments.*, pages.title AS page_title FROM comments 
                     JOIN pages ON comments.page_id = pages.page_id 
                     ORDER BY comments.created_at DESC');
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle moderation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $comment_id = $_POST['comment_id'];

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM comments WHERE comment_id = :comment_id');
        $stmt->execute([':comment_id' => $comment_id]);
        $message = "Comment deleted successfully.";
    } elseif ($action === 'hide') {
        $stmt = $pdo->prepare('UPDATE comments SET approved = FALSE WHERE comment_id = :comment_id');
        $stmt->execute([':comment_id' => $comment_id]);
        $message = "Comment hidden successfully.";
    } elseif ($action === 'disemvowel') {
        $stmt = $pdo->prepare('UPDATE comments SET content = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(content, "a", ""), "e", ""), "i", ""), "o", ""), "u", "") WHERE comment_id = :comment_id');
        $stmt->execute([':comment_id' => $comment_id]);
        $message = "Comment disemvoweled successfully.";
    }

    // Refresh the page to reflect changes
    header('Location: moderate_comments.php?message=' . urlencode($message));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Comments</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Moderate Comments</h1>

        <!-- Success Message -->
        <?php if (!empty($_GET['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Comment</th>
                    <th>Submitted By</th>
                    <th>Page</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?= htmlspecialchars($comment['content']) ?></td>
                            <td><?= $comment['user_id'] ? 'User ID: ' . $comment['user_id'] : htmlspecialchars($comment['name']) ?></td>
                            <td><?= htmlspecialchars($comment['page_title']) ?></td>
                            <td><?= htmlspecialchars($comment['created_at']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                                    <button type="submit" name="action" value="hide" class="btn btn-warning btn-sm">Hide</button>
                                    <button type="submit" name="action" value="disemvowel" class="btn btn-secondary btn-sm">Disemvowel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No comments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
