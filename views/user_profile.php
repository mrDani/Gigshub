<?php
session_start();
include '../includes/db.php';        // Database connection
include '../includes/auth_functions.php'; // Authentication utilities

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user']['user_id'] ?? null;
if (!$user_id) {
    die('User is not logged in or session is missing user ID.');
}

$message = '';

// Fetch user and profile details
$stmt = $pdo->prepare('
    SELECT u.username, u.user_type, u.email, p.*
    FROM users u
    LEFT JOIN profiles p ON u.user_id = p.user_id
    WHERE u.user_id = :user_id
');
$stmt->execute([':user_id' => $user_id]);
$user_profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_profile) {
    die('No user or profile found for the current user.');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $bio = trim($_POST['bio']);
    $skills = trim($_POST['skills_instrument']);
    $experience = trim($_POST['experience_level']);
    $location = trim($_POST['location']);
    $social_media = trim($_POST['social_media_link']);

    // Handle profile picture upload
    $profile_pic = $user_profile['profile_pic'] ?? null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profile_pictures/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $profile_pic = $upload_dir . uniqid('profile_', true) . '.' . pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic);
    }

    if ($user_profile['profile_id']) {
        // Update existing profile
        $stmt = $pdo->prepare('UPDATE profiles SET fullname = :fullname, bio = :bio, skills_instrument = :skills, 
                               experience_level = :experience, location = :location, 
                               social_media_link = :social_media, profile_pic = :profile_pic 
                               WHERE user_id = :user_id');
        $stmt->execute([
            ':fullname' => $fullname,
            ':bio' => $bio,
            ':skills' => $skills,
            ':experience' => $experience,
            ':location' => $location,
            ':social_media' => $social_media,
            ':profile_pic' => $profile_pic,
            ':user_id' => $user_id
        ]);
        $message = 'Profile updated successfully.';
    } else {
        // Insert new profile
        $stmt = $pdo->prepare('INSERT INTO profiles (user_id, fullname, bio, skills_instrument, experience_level, 
                               location, social_media_link, profile_pic) VALUES 
                               (:user_id, :fullname, :bio, :skills, :experience, :location, :social_media, :profile_pic)');
        $stmt->execute([
            ':user_id' => $user_id,
            ':fullname' => $fullname,
            ':bio' => $bio,
            ':skills' => $skills,
            ':experience' => $experience,
            ':location' => $location,
            ':social_media' => $social_media,
            ':profile_pic' => $profile_pic
        ]);
        $message = 'Profile created successfully.';
    }

    // Refresh user and profile data
    $stmt = $pdo->prepare('
        SELECT u.username, u.user_type, u.email, p.*
        FROM users u
        LEFT JOIN profiles p ON u.user_id = p.user_id
        WHERE u.user_id = :user_id
    ');
    $stmt->execute([':user_id' => $user_id]);
    $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);
}
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
        <h1 class="mb-4">User Profile</h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- User Details -->
        <div class="mb-4">
            <h4>User Information</h4>
            <p><strong>Username:</strong> <?= htmlspecialchars($user_profile['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user_profile['email']) ?></p>
            <p><strong>User Type:</strong> <?= htmlspecialchars($user_profile['user_type']) ?></p>
        </div>

        <!-- Profile Form -->
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($user_profile['fullname'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea id="bio" name="bio" class="form-control" rows="5"><?= htmlspecialchars($user_profile['bio'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="skills_instrument" class="form-label">Skills/Instrument</label>
                <input type="text" class="form-control" id="skills_instrument" name="skills_instrument" value="<?= htmlspecialchars($user_profile['skills_instrument'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="experience_level" class="form-label">Experience Level</label>
                <input type="text" class="form-control" id="experience_level" name="experience_level" value="<?= htmlspecialchars($user_profile['experience_level'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($user_profile['location'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="social_media_link" class="form-label">Social Media Link</label>
                <input type="url" class="form-control" id="social_media_link" name="social_media_link" value="<?= htmlspecialchars($user_profile['social_media_link'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="profile_pic" class="form-label">Profile Picture</label>
                <?php if (!empty($user_profile['profile_pic'])): ?>
                    <img src="<?= htmlspecialchars($user_profile['profile_pic']) ?>" alt="Profile Picture" class="img-thumbnail mb-2" width="150">
                <?php endif; ?>
                <input type="file" class="form-control" id="profile_pic" name="profile_pic">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</body>
</html>
