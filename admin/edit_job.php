<?php
session_start();
include '../includes/db.php';
include '../includes/auth_functions.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Restrict access to logged-in users
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Get the job ID
$job_id = $_GET['job_id'] ?? null;
if (!$job_id) {
    header('Location: ../views/home.php?error=Invalid job ID');
    exit();
}

// Fetch the job details
$stmt = $pdo->prepare('SELECT * FROM jobs WHERE job_id = :job_id');
$stmt->execute([':job_id' => $job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: ../views/home.php?error=Job not found');
    exit();
}

// Fetch categories
$stmt = $pdo->query('SELECT * FROM categories');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    $location = trim($_POST['location']);
    $salary = trim($_POST['salary']);

    if (empty($title) || empty($description)) {
        $error = "Title and description are required.";
    } else {
        // Update the job in the database
        $stmt = $pdo->prepare('UPDATE jobs 
                              SET title = :title, 
                                  description = :description, 
                                  category_id = :category_id, 
                                  location = :location, 
                                  salary = :salary, 
                                  updated_at = NOW() 
                              WHERE job_id = :job_id');
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':category_id' => $category_id ? $category_id : null,
            ':location' => $location,
            ':salary' => $salary,
            ':job_id' => $job_id
        ]);
        // Redirect to home.php in the views folder
        header('Location: ../views/home.php?message=Job updated successfully');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/w36mtq9l8uifu1qt5gjz41tv6mb2ar456kga97k491pic1vy/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Initialize TinyMCE for the description box
        tinymce.init({
            selector: '#description',
            plugins: 'a11ychecker advcode advlist lists link autolink autosave code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
            height: 400,
            branding: false
        });
    </script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Edit Job</h1>

        <!-- Display error message if any -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Edit Job Form -->
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Job Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($job['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Job Description</label>
                <textarea id="description" name="description" class="form-control" required><?= htmlspecialchars($job['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>" <?= $job['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($job['location'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="salary" class="form-label">Salary</label>
                <input type="text" class="form-control" id="salary" name="salary" value="<?= htmlspecialchars($job['salary'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Job</button>
        </form>
    </div>
</body>
</html>
