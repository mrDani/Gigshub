<?php
include '../includes/db.php';
include '../includes/auth_functions.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all categories and their jobs
$stmt = $pdo->query('SELECT categories.name AS category_name, categories.category_id, jobs.* 
                     FROM categories 
                     LEFT JOIN jobs ON categories.category_id = jobs.category_id 
                     ORDER BY categories.name ASC, jobs.created_at DESC');
$categories_and_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group jobs by categories
$grouped_jobs = [];
foreach ($categories_and_jobs as $row) {
    $category_name = $row['category_name'];
    $category_id = $row['category_id'];
    if (!isset($grouped_jobs[$category_name])) {
        $grouped_jobs[$category_name] = [
            'category_id' => $category_id,
            'jobs' => [],
        ];
    }
    if (!empty($row['job_id'])) {
        $grouped_jobs[$category_name]['jobs'][] = $row; // Only include jobs with valid data
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Categories and Jobs</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .tab-content .tab-pane {
            padding-top: 20px;
        }
        .job-card {
            margin-bottom: 15px;
        }
        .job-title {
            font-weight: bold;
            color: #007bff;
        }
        .job-title:hover {
            text-decoration: underline;
            color: #0056b3;
        }
        .nav-tabs .nav-link {
        color: #000 !important; 
        font-weight: bold;
        font-size: 1.1rem; 
        border: 1px solid transparent;
        border-radius: 5px;
        transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
        }
        .nav-tabs .nav-link:hover {
            color: #007bff !important;
            background-color: #333; 
            border-color: #333; 
        }
        .nav-tabs .nav-link.active {
            color: #fff; 
            background-color: #000; 
            border-color: #000; 
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); 
        }
        .nav-tabs {
            border-bottom: 2px solid #333; 
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">All Categories and Jobs</h1>
        <!-- Tabs for Categories -->
        <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
            <?php $first = true; ?>
            <?php foreach ($grouped_jobs as $category_name => $data): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $first ? 'active' : '' ?>" 
                            id="tab-<?= htmlspecialchars($data['category_id']) ?>" 
                            data-bs-toggle="tab" 
                            data-bs-target="#tab-pane-<?= htmlspecialchars($data['category_id']) ?>" 
                            type="button" role="tab" 
                            aria-controls="tab-pane-<?= htmlspecialchars($data['category_id']) ?>" 
                            aria-selected="<?= $first ? 'true' : 'false' ?>">
                        <?= htmlspecialchars($category_name) ?>
                    </button>
                </li>
                <?php $first = false; ?>
            <?php endforeach; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-4" id="categoryTabContent">
            <?php $first = true; ?>
            <?php foreach ($grouped_jobs as $category_name => $data): ?>
                <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" 
                     id="tab-pane-<?= htmlspecialchars($data['category_id']) ?>" 
                     role="tabpanel" 
                     aria-labelledby="tab-<?= htmlspecialchars($data['category_id']) ?>">
                    <?php if (count($data['jobs']) > 0): ?>
                        <?php foreach ($data['jobs'] as $job): ?>
                            <div class="card job-card">
                                <div class="card-body">
                                    <h5 class="job-title">
                                        <a href="job_details.php?job_id=<?= $job['job_id'] ?>">
                                            <?= htmlspecialchars($job['title']) ?>
                                        </a>
                                    </h5>
                                    <p class="card-text">
                                        <?= htmlspecialchars(substr($job['description'], 0, 150)) ?>...
                                    </p>
                                    <p class="text-muted small">
                                        <strong>Salary:</strong> $<?= htmlspecialchars($job['salary'] ?? 'Negotiable') ?> | 
                                        <strong>Location:</strong> <?= htmlspecialchars($job['location'] ?? 'N/A') ?>
                                    </p>
                                    <a href="job_details.php?job_id=<?= $job['job_id'] ?>" class="btn btn-primary btn-sm">View Job</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No jobs available in this category.</p>
                    <?php endif; ?>
                </div>
                <?php $first = false; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
