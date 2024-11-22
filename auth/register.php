<?php
session_start();
include '../includes/db.php';

$message = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = trim(htmlspecialchars($_POST['username'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? null;

    // Validate inputs
    if (empty($username) || strlen($username) < 3) {
        $message = 'Username must be at least 3 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
    } elseif (!$user_type || !in_array($user_type, ['job_provider', 'musician'])) {
        $message = 'Please select a valid user type.';
    } else {
        // If validation passes, hash the password and insert into the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, user_type, created_at) 
                                   VALUES (:username, :email, :password, :user_type, NOW())');
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashed_password,
                ':user_type' => $user_type
            ]);

            // Fetch the user to set session data for auto-login
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Set session data
                $_SESSION['user'] = [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'user_type' => $user['user_type'],
                ];

                // Redirect to home page
                header('Location: ../views/home.php');
                exit();
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error
                $message = 'Error: Email is already registered.';
            } else {
                $message = 'Error: ' . $e->getMessage();
            }
        }
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
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .user-type-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .user-type {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            flex: 1;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .user-type input[type="radio"] {
            display: none;
        }
        .user-type:hover {
            border-color: #007bff;
        }
        .user-type input[type="radio"]:checked + label {
            border-color: #007bff;
            background-color: #007bff;
            color: white;
        }
        .user-type label {
            display: block;
            width: 100%;
            text-align: center;
            margin: 0;
            font-weight: bold;
            padding: 5px 0;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="main">
        <div class="auth-container">
            <h2>Create an Account</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST" class="auth-form">
                <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email ?? '') ?>" required>
                <input type="password" name="password" placeholder="Password" required>

                <!-- User Type Selection -->
                <div class="user-type-container">
                    <div class="user-type">
                        <input type="radio" name="user_type" id="job_provider" value="job_provider" 
                               <?= isset($user_type) && $user_type === 'job_provider' ? 'checked' : '' ?> required>
                        <label for="job_provider">Job Provider</label>
                    </div>
                    <div class="user-type">
                        <input type="radio" name="user_type" id="musician" value="musician" 
                               <?= isset($user_type) && $user_type === 'musician' ? 'checked' : '' ?> required>
                        <label for="musician">Musician</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
        </div>
    </div>
</body>
</html>
