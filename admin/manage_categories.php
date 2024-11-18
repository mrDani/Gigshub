<?php
include '../includes/db.php';        // Database connection
include '../includes/auth_functions.php'; // Authentication utilities

// Restrict access to logged-in users with admin privileges
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle form submission for adding or updating categories
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = $_POST['name'];
    $category_id = $_POST['category_id'] ?? null;

    if (empty($category_name)) {
        $error = "Category name is required.";
    } else {
        if ($category_id) {
            // Update existing category
            $stmt = $pdo->prepare('UPDATE categories SET name = :name WHERE category_id = :category_id');
            $stmt->execute([
                ':name' => $category_name,
                ':category_id' => $category_id,
            ]);
            $message = "Category updated successfully.";
        } else {
            // Create new category
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
            $stmt->execute([':name' => $category_name]);
            $message = "Category created successfully.";
        }
    }
}

// Fetch all categories for the table
$stmt = $pdo->query('SELECT * FROM categories ORDER BY created_at DESC');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Manage Categories</h1>

        <!-- Display Success Message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Display Error Message -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Category Form -->
        <form method="POST" class="mb-4">
            <input type="hidden" name="category_id" id="category_id">
            <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter category name" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Category</button>
        </form>

        <!-- Category List -->
        <h2>Existing Categories</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td><?= htmlspecialchars($category['created_at']) ?></td>
                            <td><?= htmlspecialchars($category['updated_at']) ?></td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm edit-category-btn" 
                                        data-id="<?= $category['category_id'] ?>" 
                                        data-name="<?= htmlspecialchars($category['name']) ?>">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No categories found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Handle category edit button clicks
        document.querySelectorAll('.edit-category-btn').forEach(button => {
            button.addEventListener('click', () => {
                const categoryId = button.getAttribute('data-id');
                const categoryName = button.getAttribute('data-name');

                // Populate the form fields for editing
                document.getElementById('category_id').value = categoryId;
                document.getElementById('name').value = categoryName;
            });
        });
    </script>
</body>
</html>
