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

// Get the page ID
$page_id = $_GET['page_id'] ?? null;
if (!$page_id) {
    header('Location: ../views/home.php?error=Invalid page ID');
    exit();
}

// Fetch the page details
$stmt = $pdo->prepare('SELECT * FROM pages WHERE page_id = :page_id');
$stmt->execute([':page_id' => $page_id]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    header('Location: ../views/home.php?error=Page not found');
    exit();
}

// Fetch categories
$stmt = $pdo->query('SELECT * FROM categories');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = $_POST['category_id'];

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        // Update the page in the database
        $stmt = $pdo->prepare('UPDATE pages SET title = :title, content = :content, category_id = :category_id, updated_at = NOW() WHERE page_id = :page_id');
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':category_id' => $category_id ? $category_id : null,
            ':page_id' => $page_id,
        ]);
        // Redirect to home.php in the views folder
        header('Location: ../views/home.php?message=Page updated successfully');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Page</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/w36mtq9l8uifu1qt5gjz41tv6mb2ar456kga97k491pic1vy/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Initialize TinyMCE for the content box
        tinymce.init({
            selector: '#content',
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
        <h1 class="mb-4">Edit Page</h1>

        <!-- Display error message if any -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Edit Page Form -->
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($page['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea id="content" name="content" class="form-control" required><?= htmlspecialchars($page['content']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>" <?= $page['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Page</button>
        </form>
    </div>
</body>
</html>
