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

// Fetch recent jobs for display
try {
    $stmt = $pdo->query('SELECT jobs.*, categories.name AS category_name FROM jobs 
                         LEFT JOIN categories ON jobs.category_id = categories.category_id 
                         ORDER BY jobs.created_at DESC LIMIT 5');
    $recent_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_jobs = [];
    $job_error = $e->getMessage();
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
    <style>
        /* Styles remain the same as your original design */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .welcome-heading {
            font-size: 3rem;
            font-weight: bold;
            color: #007bff;
            text-transform: uppercase;
            animation: fadeIn 1s ease-in-out;
        }

        .welcome-description {
            font-size: 1.2rem;
            margin-top: 10px;
            color: #555;
            animation: fadeIn 1.5s ease-in-out;
        }

        .btn-primary, .btn-secondary {
            padding: 12px 25px;
            font-size: 1.1rem;
            border-radius: 30px;
            transition: transform 0.3s, background-color 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            background-color: #0056b3;
        }

        .btn-secondary:hover {
            transform: translateY(-5px);
            background-color: #6c757d;
        }

        .section-heading {
            font-size: 2rem;
            margin-top: 30px;
            color: #333;
            font-weight: bold;
            text-align: center;
        }

        .recent-listing {
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            transition: transform 0.3s, background-color 0.3s;
        }

        .recent-listing:hover {
            transform: translateY(-5px);
            background-color: #f8f9fa;
        }

        .listing-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #007bff;
            margin: 0;
        }

        .listing-content {
            margin: 5px 0;
            font-size: 1rem;
            color: #555;
        }

        footer {
            text-align: center;
            padding: 20px 0;
            background-color: #333;
            color: #fff;
            margin-top: 50px;
        }

        footer a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s;
        }

        footer a:hover {
            color: #0056b3;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
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

    <!-- Recent Listings Section -->
    <div class="container mt-5">
        <h2 class="section-heading">Recent Jobs</h2>
        <div class="list-group">
            <?php if (!empty($recent_jobs)): ?>
                <?php foreach ($recent_jobs as $job): ?>
                    <a href="./views/job_details.php?job_id=<?= $job['job_id'] ?>" class="list-group-item list-group-item-action recent-listing">
                        <h5 class="listing-title"><?= htmlspecialchars($job['title']) ?></h5>
                        <p class="listing-content">
                            <?= htmlspecialchars(substr(strip_tags($job['description']), 0, 100)) ?>...
                        </p>
                        <small>
                            Category: <?= htmlspecialchars($job['category_name'] ?? 'Uncategorized') ?> | 
                            Created: <?= htmlspecialchars($job['created_at']) ?>
                        </small>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No recent jobs available.</p>
                <?php if (isset($job_error)): ?>
                    <p class="text-danger">Error: <?= htmlspecialchars($job_error) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> Gigshub. All rights reserved. <a href="./views/home.php">Browse All Jobs</a></p>
    </footer>
</body>
</html>
