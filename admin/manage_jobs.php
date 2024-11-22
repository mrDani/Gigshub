<?php
include '../includes/db.php';        // Database connection
include '../includes/auth_functions.php'; // Authentication utilities

// Restrict access to logged-in users with admin privileges
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        // Insert the new page into the database
        $stmt = $pdo->prepare('INSERT INTO pages (title, content, category_id) VALUES (:title, :content, :category_id)');
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':category_id' => $category_id ? $category_id : null,
        ]);
        $message = "Page created successfully.";
    }
}

// Fetch categories for the dropdown
$stmt = $pdo->query('SELECT * FROM categories');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Page</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
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
        <h1 class="mb-4">Create New Page</h1>

        <!-- Display Success Message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Display Error Message -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea id="content" name="content" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Page</button>
        </form>
    </div>
</body>
</html>
