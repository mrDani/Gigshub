<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Ensure session is active
}

// Generate a new CAPTCHA code
$captcha_code = rand(1000, 9999);
$_SESSION['captcha'] = $captcha_code;

// Debug: Log the CAPTCHA value
error_log("Generated CAPTCHA: " . $captcha_code);

// Create the CAPTCHA image
header('Content-Type: image/png');
$image_width = 120;
$image_height = 40;

$image = imagecreate($image_width, $image_height);

if (!$image) {
    error_log("Error: Failed to create image.");
    exit;
}

// Colors
$background_color = imagecolorallocate($image, 255, 255, 255); // White
$text_color = imagecolorallocate($image, 0, 0, 0);             // Black
$noise_color = imagecolorallocate($image, 200, 200, 200);      // Light gray

// Add random noise
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $image_width), rand(0, $image_height), $noise_color);
}

// Add the CAPTCHA text
$font_size = 5;
$x_position = rand(10, 30);
$y_position = rand(5, 15);
imagestring($image, $font_size, $x_position, $y_position, $captcha_code, $text_color);

// Output the image
imagepng($image);
imagedestroy($image);
?>
