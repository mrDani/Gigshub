<?php
session_start();
include '../includes/db.php';        // Database connection
include '../includes/auth_functions.php'; // Authentication utilities

// Restrict access to logged-in users
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Get the current user's type from the session
$user_type = $_SESSION['user']['user_type'] ?? null;

if (!$user_type) {
    die('User type is not set in the session. Please log in again.');
}

// Fetch categories for category navigation
$stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination Setup
$limit = 10; // Number of jobs per page
$page = $_GET['page'] ?? 1; // Current page number
$page = max(1, intval($page)); // Ensure the page number is at least 1
$offset = ($page - 1) * $limit; // Offset for SQL query

// Determine sorting and optional category filter
$order_by = $_GET['order_by'] ?? 'created_at';
$category_id = $_GET['category_id'] ?? null;

$valid_columns = ['title', 'created_at', 'updated_at', 'salary'];
if (!in_array($order_by, $valid_columns)) {
    $order_by = 'created_at';
}

// Prepare SQL query with optional category filter for jobs
if ($category_id) {
    $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS jobs.*, categories.name AS category_name FROM jobs 
                           LEFT JOIN categories ON jobs.category_id = categories.category_id 
                           WHERE jobs.category_id = :category_id 
                           ORDER BY $order_by DESC 
                           LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
} else {
    $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS jobs.*, categories.name AS category_name FROM jobs 
                           LEFT JOIN categories ON jobs.category_id = categories.category_id 
                           ORDER BY $order_by DESC 
                           LIMIT :limit OFFSET :offset");
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of jobs for pagination
$total_jobs = $pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
$total_pages = ceil($total_jobs / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs List</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Jobs List</h1>

        <!-- "Create New Job" Button for Admin and Job Provider -->
        <?php if ($user_type === 'admin' || $user_type === 'job_provider'): ?>
            <div class="mb-4">
                <a href="../admin/new_job.php" class="btn btn-success">Create New Job</a>
            </div>
        <?php endif; ?>

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

        <!-- Jobs List -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><a href="?order_by=title<?= $category_id ? "&category_id=$category_id" : '' ?>" class="text-decoration-none">Title</a></th>
                    <th><a href="?order_by=salary<?= $category_id ? "&category_id=$category_id" : '' ?>" class="text-decoration-none">Salary</a></th>
                    <th><a href="?order_by=created_at<?= $category_id ? "&category_id=$category_id" : '' ?>" class="text-decoration-none">Created At</a></th>
                    <th><a href="?order_by=updated_at<?= $category_id ? "&category_id=$category_id" : '' ?>" class="text-decoration-none">Updated At</a></th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($jobs) > 0): ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <a href="job_details.php?job_id=<?= $job['job_id'] ?>" class="text-decoration-none text-primary">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($job['salary'] ?? 'Negotiable') ?></td>
                            <td><?= htmlspecialchars($job['created_at']) ?></td>
                            <td><?= htmlspecialchars($job['updated_at']) ?></td>
                            <td><?= htmlspecialchars($job['category_name'] ?? 'Uncategorized') ?></td>
                            <td>
                                <a href="job_details.php?job_id=<?= $job['job_id'] ?>" class="btn btn-primary btn-sm">View</a>
                                <!-- Show Edit and Delete Buttons for Admin and Job Provider -->
                                <?php if ($user_type === 'admin' || $user_type === 'job_provider'): ?>
                                    <a href="../admin/edit_job.php?job_id=<?= $job['job_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="../admin/delete_job.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this job?')">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No jobs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&order_by=<?= htmlspecialchars($order_by) ?>&category_id=<?= htmlspecialchars($category_id) ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&order_by=<?= htmlspecialchars($order_by) ?>&category_id=<?= htmlspecialchars($category_id) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&order_by=<?= htmlspecialchars($order_by) ?>&category_id=<?= htmlspecialchars($category_id) ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</body>
</html>
