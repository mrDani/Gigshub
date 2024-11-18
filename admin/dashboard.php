<?php
include '../includes/db.php';
include '../includes/auth_functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch pages
$order_by = $_GET['order_by'] ?? 'created_at';
$valid_columns = ['title', 'created_at', 'updated_at'];

if (!in_array($order_by, $valid_columns)) {
    $order_by = 'created_at';
}

$stmt = $pdo->prepare("SELECT pages.*, categories.name AS category_name FROM pages 
                       LEFT JOIN categories ON pages.category_id = categories.category_id 
                       ORDER BY $order_by DESC");
$stmt->execute();
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Admin Dashboard</h1>
        <a href="manage_jobs.php" class="btn btn-success mb-3">Create New Page</a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><a href="?order_by=title">Title</a></th>
                    <th><a href="?order_by=created_at">Created At</a></th>
                    <th><a href="?order_by=updated_at">Updated At</a></th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <tr>
                        <td><?= htmlspecialchars($page['title']) ?></td>
                        <td><?= htmlspecialchars($page['created_at']) ?></td>
                        <td><?= htmlspecialchars($page['updated_at']) ?></td>
                        <td><?= htmlspecialchars($page['category_name'] ?? 'Uncategorized') ?></td>
                        <td>
                            <a href="edit_page.php?page_id=<?= $page['page_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <form action="delete_page.php" method="POST" style="display:inline;">
                                <input type="hidden" name="page_id" value="<?= $page['page_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
