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
    $cover_letter = trim($_POST['cover_letter']);
    $user_id = $_SESSION['user']['user_id'];
    $resume_path = null;

    // Handle resume upload if provided
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/resumes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
        }

        $resume_name = uniqid('resume_', true) . '.' . pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
        $resume_path = $upload_dir . $resume_name;

        if (move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
            $resume_path = $resume_path; // Store path in the database
        } else {
            $message = 'Error uploading resume. Please try again.';
        }
    }

    // Insert application into the database
    try {
        $stmt = $pdo->prepare('INSERT INTO applications (job_id, user_id, cover_letter, resume) 
                               VALUES (:job_id, :user_id, :cover_letter, :resume)');
        $stmt->execute([
            ':job_id' => $job_id,
            ':user_id' => $user_id,
            ':cover_letter' => $cover_letter,
            ':resume' => $resume_path
        ]);

        $message = 'Your application has been submitted!';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
    }
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
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="cover_letter" class="form-label">Cover Letter</label>
                <textarea id="cover_letter" name="cover_letter" class="form-control" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="resume" class="form-label">Resume (Optional)</label>
                <input type="file" id="resume" name="resume" class="form-control" accept=".pdf,.doc,.docx">
            </div>
            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
    </div>
</body>
</html>
