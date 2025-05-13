<!-- Account Activation Page (English) -->

<!-- PHP code -->
<?php
// Start the session
session_start();

// Retrieve token (preserve across refresh)
if (isset($_GET['token'])) {
    $_SESSION['token'] = $_GET['token'];
}

// Ensure token is present
$token = $_SESSION['token'] ?? '';
if (empty($token)) {
    die("Invalid or expired token.");
}

$token_hash = hash('sha256', $token);

// Include database connection
$mysqli = require __DIR__ . '/database.php';

// Check if the token exists in the database before activating the account
$sql = "SELECT * FROM users WHERE account_activation_hash = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Invalid or expired token.");
}

// Only activate the account if it hasn't been activated before
if ($user['account_activation_hash'] !== null) {
    $sql = "UPDATE users SET account_activation_hash = NULL WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $user['user_id']);
    $stmt->execute();
}

// Get error message from session (if any)
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']); // Remove after displaying
?>

<!-- HTML code -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activated</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="dashboard/images/logo-favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <div class="name-lang">
            <header><span>Gluco</span>Predictor</header>
        </div>

        <div class="text-center mt-2">
            <p>Account activated successfully. You can now <a href="login.php">log in</a>.</p>
        </div>
    </div>
</body>


</html>