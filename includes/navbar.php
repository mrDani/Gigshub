<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Ensure session is active
}

$base_url = '/wd2/Project/GigsHub'; // Adjust this to match your URL structure
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs in <?= htmlspecialchars($category['name'] ?? 'All Categories') ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Navbar Styling */
        .navbar {
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
            background: linear-gradient(90deg, #007bff, #4a90e2) !important;
            padding: 0.8rem 1.5rem;
        }

        .navbar-brand {
            color: #fff !important;
            font-weight: bold;
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .navbar-brand:hover {
            color: #ffd700 !important;
            transform: scale(1.1);
        }

        .nav-link {
            color: #e0e0e0 !important;
            font-size: 1rem;
            margin-right: 1rem;
            text-transform: capitalize;
            position: relative;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: #ffd700;
            bottom: -2px;
            left: 50%;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover {
            color: #fff !important;
            transform: scale(1.05);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .navbar-text {
            font-size: 1rem;
            color: #f8f9fa;
            font-style: italic;
            margin-left: auto;
            letter-spacing: 0.5px;
            transition: color 0.3s ease;
        }

        .navbar-text:hover {
            color: #ffd700;
        }
    </style>
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $base_url ?>/index.php">GigsHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base_url ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base_url ?>/views/category_jobs.php">Categories</a>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/views/user_profile.php">Profile</a>
                    </li>
                    <?php if ($_SESSION['user']['user_type'] === 'job_provider'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>/views/applications.php">Applications</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/auth/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>/auth/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
            <?php if (isset($_SESSION['user'])): ?>
                <span class="navbar-text">
                    Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?>!
                </span>
            <?php endif; ?>
        </div>
    </div>
</nav>
