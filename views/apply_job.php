<?php
include '../includes/db.php';
include '../includes/auth_functions.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

$job_id = $_GET['job_id'] ?? null;

if (!$job_id) {
    header('Location: home.php?error=Job not found');
    exit();
}

// Fetch job details
$stmt = $pdo->prepare('SELECT * FROM jobs WHERE job_id = :job_id');
$stmt->execute([':job_id' => $job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: home.php?error=Job not found');
    exit();
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_letter = $_POST['cover_letter'];
    $user_id = $_SESSION['user']['user_id'];

    $stmt = $pdo->prepare('INSERT INTO applications (job_id, user_id, cover_letter) VALUES (:job_id, :user_id, :cover_letter)');
    $stmt->execute([
        ':job_id' => $job_id,
        ':user_id' => $user_id,
        ':cover_letter' => $cover_letter
    ]);

    $message = 'Your application has been submitted!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?= htmlspecialchars($job['title']) ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Apply for <?= htmlspecialchars($job['title']) ?></h1>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="cover_letter" class="form-label">Cover Letter</label>
                <textarea id="cover_letter" name="cover_letter" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
    </div>
</body>
</html>
