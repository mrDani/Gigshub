<?php
include '../includes/db.php';
include '../includes/auth_functions.php';

// Restrict to logged-in users
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user']['user_id'];

// Fetch user profile details
$stmt = $pdo->prepare('SELECT * FROM profiles WHERE user_id = :user_id');
$stmt->execute([':user_id' => $user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4">Profile</h1>
        <table class="table table-bordered">
            <tr>
                <th>Full Name</th>
                <td><?= htmlspecialchars($profile['fullname']) ?></td>
            </tr>
            <tr>
                <th>Bio</th>
                <td><?= htmlspecialchars($profile['bio']) ?></td>
            </tr>
            <tr>
                <th>Skills</th>
                <td><?= htmlspecialchars($profile['skills_instrument']) ?></td>
            </tr>
            <tr>
                <th>Experience Level</th>
                <td><?= htmlspecialchars($profile['experience_level']) ?></td>
            </tr>
                <th>Location</th>
                <td><?= htmlspecialchars($profile['location']) ?></td>
            </tr>
            <tr>
                <th>Social Media</th>
                <td><a href="<?= htmlspecialchars($profile['social_media_link']) ?>" target="_blank"><?= htmlspecialchars($profile['social_media_link']) ?></a></td>
            </tr>
        </table>
    </div>
</body>
</html>
