<?php
session_start();
include '../includes/db.php';
include '../includes/auth_functions.php';

$message = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password'] ?? '');

    // Validate inputs
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } elseif (empty($password)) {
        $message = 'Password is required.';
    } else {
        // Fetch user from the database
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Store user data in the session
            $_SESSION['user'] = [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'user_type' => $user['user_type'], // Ensure user_type is included
            ];

            // Redirect to the home page
            header('Location: ../views/home.php');
            exit();
        } else {
            $message = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gigshub</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/auth_styles.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
    <div class="main">
        <div class="auth-container">
            <h2>Login to Gigshub</h2>
            <?php if ($message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST" class="auth-form">
                <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email ?? '') ?>" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="mt-3">Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </div>
</body>
</html>
