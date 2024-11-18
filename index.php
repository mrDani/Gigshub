<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
include './includes/db.php';        // Database connection
include './includes/auth_functions.php'; // Authentication utilities

// Fetch categories for navigation
try {
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    $category_error = $e->getMessage();
}

// Fetch recent pages for display
try {
    $stmt = $pdo->query('SELECT pages.*, categories.name AS category_name FROM pages 
                         LEFT JOIN categories ON pages.category_id = categories.category_id 
                         ORDER BY pages.created_at DESC LIMIT 5');
    $recent_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_pages = [];
    $page_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gigshub - Welcome</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <div class="container mt-5 text-center">
        <h1 class="welcome-heading">Welcome to Gigshub</h1>
        <p class="lead welcome-description">The ultimate job marketplace for musicians and performers.</p>
        <?php if (!isLoggedIn()): ?>
            <a href="./auth/login.php" class="btn btn-primary btn-lg">Login</a>
            <a href="./auth/register.php" class="btn btn-secondary btn-lg">Register</a>
        <?php else: ?>
            <a href="./views/home.php" class="btn btn-primary btn-lg">Browse Jobs</a>
        <?php endif; ?>
    </div>

    <!-- Categories Section -->
    <div class="container mt-5">
        <h2 class="section-heading">Explore Categories</h2>
        <div class="d-flex flex-wrap justify-content-center">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <a href="./views/home.php?category_id=<?= $category['category_id'] ?>" class="category-btn">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No categories available.</p>
                <?php if (isset($category_error)): ?>
                    <p class="text-danger">Error: <?= htmlspecialchars($category_error) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Listings Section -->
    <div class="container mt-5">
        <h2 class="section-heading">Recent Listings</h2>
        <div class="list-group">
            <?php if (!empty($recent_pages)): ?>
                <?php foreach ($recent_pages as $page): ?>
                    <a href="./views/job_details.php?page_id=<?= $page['page_id'] ?>" class="list-group-item list-group-item-action recent-listing">
                        <h5 class="listing-title"><?= htmlspecialchars($page['title']) ?></h5>
                        <p class="listing-content"><?= htmlspecialchars(substr(strip_tags($page['content']), 0, 100)) ?>...</p>
                        <small>Category: <?= htmlspecialchars($page['category_name'] ?? 'Uncategorized') ?> | Created: <?= htmlspecialchars($page['created_at']) ?></small>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No recent listings available.</p>
                <?php if (isset($page_error)): ?>
                    <p class="text-danger">Error: <?= htmlspecialchars($page_error) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> Gigshub. All rights reserved. <a href="./views/home.php">Browse All Jobs</a></p>
    </footer>
</body>
</html>
