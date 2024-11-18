<?php
session_start();
include '../includes/db.php';

$message = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password
        ]);
        $message = 'Registration successful. You can now <a href="login.php">login</a>.';
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Gigshub</title>
    <link rel="stylesheet" href="../css/auth_styles.css">
</head>
<body>
    <div class="auth-container">
        <h2>Create an Account</h2>
        <?php if ($message): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
