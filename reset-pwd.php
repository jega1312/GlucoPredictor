<!-- Reset Password Page -->

<!-- PHP code -->
<?php
// Start the session
session_start();

// Handle language selection
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ms'])) {
    $_SESSION['lang'] = $_GET['lang'];
    header("Location: reset-pwd.php?token=" . urlencode($_GET['token'])); // Preserve token on redirection
    exit;
}

// Default language
$lang = $_SESSION['lang'] ?? 'en';

// Translations
$text = [
    'en' => [
        'title' => 'Reset Password',
        'invalid_token' => 'Invalid or expired token.',
        'expired_token' => 'Token has expired.',
        'enter_new_password' => 'Enter new password',
        'confirm_new_password' => 'Confirm new password',
        'reset' => 'Reset',
    ],
    'ms' => [
        'title' => 'Tetapkan Semula Kata Laluan',
        'invalid_token' => 'Token tidak sah atau telah tamat tempoh.',
        'expired_token' => 'Token telah tamat tempoh.',
        'enter_new_password' => 'Masukkan kata laluan baru',
        'confirm_new_password' => 'Sahkan kata laluan baru',
        'reset' => 'Tetapkan semula',
    ]
];

// Retrieve token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die($text[$lang]['invalid_token']);
}

$token_hash = hash('sha256', $token);

// Include database connection
$mysqli = require __DIR__ . '/database.php';

$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die($text[$lang]['invalid_token']);
}

if (strtotime($user['reset_token_expires_at']) <= time()) {
    die($text[$lang]['expired_token']);
}

// Get error message from session (if any)
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']); // Remove after displaying
?>

<!-- HTML code -->
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $text[$lang]['title']; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="dashboard/images/logo-favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <div class="name-lang">
            <header><span>Gluco</span>Predictor</header>
            <div class="text-center mt-2">
                <div class="lang-switch">
                    <a href="?token=<?= urlencode($token) ?>&lang=en">EN</a> |
                    <a href="?token=<?= urlencode($token) ?>&lang=ms">BM</a>
                </div>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form action="process-reset-pwd.php" method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="form-group">
                <input type="password" class="form-control" name="new_password" placeholder="<?= $text[$lang]['enter_new_password']; ?>" id="new_password" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="repeat-pwd" placeholder="<?= $text[$lang]['confirm_new_password']; ?>" id="repeat-pwd" required>
            </div>
            <div class="form-btn">
                <input type="submit" value="<?= $text[$lang]['reset']; ?>" name="submit" class="btn btn-primary">
            </div>
        </form>
    </div>
</body>

</html>