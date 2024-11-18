<?php
include '../includes/db.php';
include '../includes/auth_functions.php';

$category_id = $_GET['category_id'] ?? null;

// Redirect if no category is selected
if (!$category_id) {
    header('Location: home.php?error=Category not found');
    exit();
}

// Fetch category name
$stmt = $pdo->prepare('SELECT name FROM categories WHERE category_id = :category_id');
$stmt->execute([':category_id' => $category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: home.php?error=Category not found');
    exit();
}

// Fetch jobs by category
$stmt = $pdo->prepare('SELECT * FROM jobs WHERE category_id = :category_id ORDER BY created_at DESC');
$stmt->execute([':category_id' => $category_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs in <?= htmlspecialchars($category['name']) ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Jobs in <?= htmlspecialchars($category['name']) ?></h1>
        <?php if (count($jobs) > 0): ?>
            <ul class="list-group">
                <?php foreach ($jobs as $job): ?>
                    <li class="list-group-item">
                        <h5><?= htmlspecialchars($job['title']) ?></h5>
                        <p><?= htmlspecialchars($job['description']) ?></p>
                        <a href="job_details.php?job_id=<?= $job['job_id'] ?>" class="btn btn-primary">View Job</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No jobs available in this category.</p>
        <?php endif; ?>
    </div>
</body>
</html>
