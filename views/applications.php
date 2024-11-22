<?php
session_start();
include '../includes/db.php';
include '../includes/auth_functions.php';

// Restrict access to logged-in job providers
if (!isLoggedIn() || $_SESSION['user']['user_type'] !== 'job_provider') {
    header('Location: ../auth/login.php');
    exit();
}

// Get the current job provider's ID
$job_provider_id = $_SESSION['user']['user_id'];

// Fetch all jobs posted by the job provider
$stmt = $pdo->prepare('SELECT * FROM jobs WHERE user_id = :job_provider_id');
$stmt->execute([':job_provider_id' => $job_provider_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$jobs) {
    echo '<p>No jobs found for this job provider.</p>';
    exit();
}

// Fetch all applications for jobs posted by the job provider
$stmt = $pdo->prepare('SELECT applications.*, jobs.title AS job_title, users.username AS applicant_name 
                       FROM applications 
                       LEFT JOIN jobs ON applications.job_id = jobs.job_id 
                       LEFT JOIN users ON applications.user_id = users.user_id 
                       WHERE jobs.user_id = :job_provider_id
                       ORDER BY applications.application_date DESC');
$stmt->execute([':job_provider_id' => $job_provider_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - Job Provider</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Applications for Your Jobs</h1>

        <?php if (!empty($applications)): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Applicant Name</th>
                        <th>Cover Letter</th>
                        <th>Resume</th>
                        <th>Application Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $application): ?>
                        <tr>
                            <td><?= htmlspecialchars($application['job_title']) ?></td>
                            <td><?= htmlspecialchars($application['applicant_name']) ?></td>
                            <td><?= nl2br(htmlspecialchars($application['cover_letter'])) ?></td>
                            <td>
                                <?php if (!empty($application['resume'])): ?>
                                    <a href="<?= htmlspecialchars($application['resume']) ?>" target="_blank">View Resume</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($application['application_date']) ?></td>
                            <td><?= htmlspecialchars($application['status'] ?? 'Pending') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No applications found for your jobs.</p>
        <?php endif; ?>
    </div>
</body>
</html>
