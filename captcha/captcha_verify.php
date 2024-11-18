<?php
session_start();

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = $_POST['captcha_input'] ?? '';

    // Check if the CAPTCHA matches
    if (isset($_SESSION['captcha']) && $_SESSION['captcha'] === $user_input) {
        $response['success'] = true;
        $response['message'] = 'CAPTCHA verified successfully.';
        unset($_SESSION['captcha']); // Clear CAPTCHA after verification
    } else {
        $response['message'] = 'CAPTCHA verification failed. Please try again.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
