<?php
session_start();
include '../includes/db.php';

$message = '';

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('UPDATE users SET password = :new_password WHERE email = :email');
        $stmt->execute([
            ':new_password' => $new_password,
            ':email' => $email
        ]);
        $message = 'Password reset successful. You can now <a href="login.php">login</a>.';
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
    <title>Reset Password - Gigshub</title>
    <link rel="stylesheet" href="../css/auth_styles.css">
</head>
<body>
    <div class="auth-container">
        <h2>Reset Your Password</h2>
        <?php if ($message): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit" class="btn">Reset Password</button>
        </form>
        <p>Remembered your password? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
