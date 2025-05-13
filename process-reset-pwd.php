<!-- Reset Password Process -->

<!-- PHP code -->
<?php
session_start();

// Default language to English if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Language options
$lang = $_SESSION['lang'];
$messages = [
    'en' => [
        'invalid_token' => "Invalid or expired token.",
        'expired_token' => "Token has expired.",
        'short_password' => "Password must be at least 8 characters long.",
        'uppercase_required' => "Password must contain at least one uppercase letter.",
        'number_required' => "Password must contain at least one number.",
        'password_mismatch' => "Passwords do not match.",
        'password_reset_success' => "Password reset successfully. You can now log in."
    ],
    'ms' => [
        'invalid_token' => "Token tidak sah atau telah tamat tempoh.",
        'expired_token' => "Token telah tamat tempoh.",
        'short_password' => "Kata laluan mesti sekurang-kurangnya 8 aksara.",
        'uppercase_required' => "Kata laluan mesti mengandungi sekurang-kurangnya satu huruf besar.",
        'number_required' => "Kata laluan mesti mengandungi sekurang-kurangnya satu nombor.",
        'password_mismatch' => "Kata laluan tidak sepadan.",
        'password_reset_success' => "Tetapan semula kata laluan berjaya. Anda kini boleh log masuk."
    ]
];

$token = $_POST['token'] ?? '';

if (empty($token)) {
    $_SESSION['error_message'] = $messages[$lang]['invalid_token'];
    header("Location: reset-pwd.php?token=" . urlencode($token));
    exit;
}

$token_hash = hash('sha256', $token);
$mysqli = require __DIR__ . '/database.php';

$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = $messages[$lang]['invalid_token'];
    header("Location: reset-pwd.php?token=" . urlencode($token));
    exit;
}

if (strtotime($user['reset_token_expires_at']) <= time()) {
    $_SESSION['error_message'] = $messages[$lang]['expired_token'];
    header("Location: reset-pwd.php?token=" . urlencode($token));
    exit;
}

$new_password = $_POST["new_password"] ?? '';
$repeat_password = $_POST["repeat-pwd"] ?? '';

if (strlen($new_password) < 8) {
    $_SESSION['error_message'] = $messages[$lang]['short_password'];
    header("Location: reset-pwd.php?token=" . urlencode($token));
    exit;
}

if (!preg_match("/[A-Z]/", $new_password)) {
    $_SESSION['error_message'] = $messages[$lang]['uppercase_required'];
    header("Location: reset-pwd.php?token=" . urlencode($token));
    exit;
}

if (!preg_match("/[0-9]/", $new_password)) {
    $_SESSION['error_message'] = $messages[$lang]['number_required'];
    header("Location: reset-pwd.php?token=" . urlencode($token));
    exit;
}

if ($new_password !== $repeat_password) {
    $_SESSION['error_message'] = $messages[$lang]['password_mismatch'];
    header("Location: reset-pwd.php?token=" . urlencode($token));
    exit;
}

$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('si', $password_hash, $user['user_id']);
$stmt->execute();

header("Location: login.php");
exit;
