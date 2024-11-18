<?php
// admin/new_page.php
session_start();
include '../includes/db.php'; // Database connection
include '../includes/auth_functions.php'; // Authentication check

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Restrict access to logged-in users
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = $_POST['category_id'];

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        // Insert the new page into the database
        $stmt = $pdo->prepare("INSERT INTO pages (title, content, category_id, created_at, updated_at) 
                               VALUES (:title, :content, :category_id, NOW(), NOW())");
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'category_id' => $category_id ? $category_id : null,
        ]);

        // Redirect to home.php in the views folder
        header('Location: ../views/home.php?message=Page created successfully');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/w36mtq9l8uifu1qt5gjz41tv6mb2ar456kga97k491pic1vy/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Initialize TinyMCE for the content box
        document.addEventListener('DOMContentLoaded', () => {
            tinymce.init({
                selector: '#content',
                plugins: 'a11ychecker advcode advlist lists link autolink autosave code',
                toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
                height: 400,
                branding: false
            });

            // Attach event listener to sync TinyMCE with textarea before form submission
            const form = document.querySelector('form');
            form.addEventListener('submit', (e) => {
                tinymce.triggerSave(); // Ensure TinyMCE content is saved to the textarea

                // Revalidate the textarea if empty
                const textarea = document.querySelector('#content');
                if (!textarea.value.trim()) {
                    alert('Content is required.');
                    e.preventDefault(); // Prevent form submission
                    textarea.focus(); // Focus on the TinyMCE editor
                }
            });
        });
    </script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="mb-4">Create New Page</h1>

        <!-- Display error message if any -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="new_page.php" method="POST" novalidate>
            <div class="mb-3">
                <label for="title" class="form-label">Page Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select name="category_id" id="category" class="form-select">
                    <option value="">No Category</option>
                    <?php
                    // Fetch categories for the dropdown
                    $stmt = $pdo->query('SELECT * FROM categories');
                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Page</button>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
