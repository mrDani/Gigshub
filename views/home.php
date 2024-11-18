<?php
session_start();
include '../includes/db.php';        // Database connection
include '../includes/auth_functions.php'; // Authentication utilities

// Restrict access to logged-in users
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch categories for category navigation
$stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine sorting and optional category filter
$order_by = $_GET['order_by'] ?? 'created_at';
$category_id = $_GET['category_id'] ?? null;

$valid_columns = ['title', 'created_at', 'updated_at'];
if (!in_array($order_by, $valid_columns)) {
    $order_by = 'created_at';
}

// Prepare SQL query with optional category filter
if ($category_id) {
    $stmt = $pdo->prepare("SELECT pages.*, categories.name AS category_name FROM pages 
                           LEFT JOIN categories ON pages.category_id = categories.category_id 
                           WHERE pages.category_id = :category_id 
                           ORDER BY $order_by DESC");
    $stmt->execute([':category_id' => $category_id]);
} else {
    $stmt = $pdo->prepare("SELECT pages.*, categories.name AS category_name FROM pages 
                           LEFT JOIN categories ON pages.category_id = categories.category_id 
                           ORDER BY $order_by DESC");
    $stmt->execute();
}

$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages List</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Pages List</h1>

        <!-- "Create New Page" Button for All Logged-In Users -->
        <div class="mb-4">
            <a href="../admin/new_page.php" class="btn btn-success">Create New Page</a>
        </div>

        <!-- Category Navigation -->
        <div class="mb-4">
            <h4>Filter by Category</h4>
            <ul class="list-inline">
                <li class="list-inline-item">
                    <a href="home.php" class="btn btn-outline-secondary <?= empty($category_id) ? 'active' : '' ?>">All</a>
                </li>
                <?php foreach ($categories as $category): ?>
                    <li class="list-inline-item">
                        <a href="home.php?category_id=<?= $category['category_id'] ?>&order_by=<?= htmlspecialchars($order_by) ?>" 
                           class="btn btn-outline-secondary <?= $category_id == $category['category_id'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Display Sorting -->
        <p class="text-muted">Sorted by: <?= htmlspecialchars($order_by) ?></p>

        <!-- Pages List -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><a href="?order_by=title<?= $category_id ? "&category_id=$category_id" : '' ?>" class="text-decoration-none">Title</a></th>
                    <th><a href="?order_by=created_at<?= $category_id ? "&category_id=$category_id" : '' ?>" class="text-decoration-none">Created At</a></th>
                    <th><a href="?order_by=updated_at<?= $category_id ? "&category_id=$category_id" : '' ?>" class="text-decoration-none">Updated At</a></th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pages) > 0): ?>
                    <?php foreach ($pages as $page): ?>
                        <tr>
                            <td><?= htmlspecialchars($page['title']) ?></td>
                            <td><?= htmlspecialchars($page['created_at']) ?></td>
                            <td><?= htmlspecialchars($page['updated_at']) ?></td>
                            <td><?= htmlspecialchars($page['category_name'] ?? 'Uncategorized') ?></td>
                            <td>
                                <a href="job_details.php?page_id=<?= $page['page_id'] ?>" class="btn btn-primary btn-sm">View</a>
                                <!-- Show Edit and Delete Buttons for Logged-In Users -->
                                <a href="../admin/edit_page.php?page_id=<?= $page['page_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <form action="../admin/delete_page.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="page_id" value="<?= $page['page_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this page?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No pages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
