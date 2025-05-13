<!-- Email Verification Page - Reset Password -->

<!-- PHP code -->
<?php
// Start the session
session_start();

// Handle language selection
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ms'])) {
    $_SESSION['lang'] = $_GET['lang'];
    header("Location: forgot-pwd.php"); // Redirect to apply changes
    exit;
}

// Default language
$lang = $_SESSION['lang'] ?? 'en';

// Translations
$translations = [
    'en' => [
        'title' => 'Reset Password Email Verification',
        'enter_email' => 'Enter your email address',
        'send' => 'Send',
        'success_message' => 'Message sent, please check your email, including spam and junk folders.',
        'error_email_required' => 'Error: Email is required.',
        'reset_email_message' => "Click <a href='http://localhost/glucopredictor/reset-pwd.php?email={email}&token={token}&lang=en'>here</a> to reset your password.",
        'reset_email_subject' => 'GlucoPredictor Password Reset',
        'email_not_found_message' => 'Email not registered.',
    ],
    'ms' => [
        'title' => 'Pengesahan E-mel Tetapan Semula Kata Laluan',
        'enter_email' => 'Masukkan alamat emel anda',
        'send' => 'Hantar',
        'success_message' => 'Mesej telah dihantar, sila semak emel anda, termasuk penyata spam dan sampah.',
        'error_email_required' => 'Ralat: Emel diperlukan.',
        'reset_email_message' => "Klik di <a href='http://localhost/glucopredictor/reset-pwd.php?email={email}&token={token}&lang=ms'> sini</a> untuk menetapkan semula kata laluan anda.",
        'reset_email_subject' => 'Tetapan Semula Kata Laluan GlucoPredictor',
        'email_not_found_message' => 'Emel tidak didaftar.',
    ]
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if email is set
    $email = $_POST['email'] ?? null;

    if (!$email) {
        die($translations[$lang]['error_email_required']);
    }

    // Generate token
    $token = bin2hex(random_bytes(16));
    $token_hash = hash('sha256', $token);
    $expiry = date('Y-m-d H:i:s', time() + 60 * 30);

    // Include the database connection
    $conn = require __DIR__ . '/database.php';

    // Ensure database connection is valid
    if (!($conn instanceof mysqli)) {
        die("Database connection error.");
    }

    // Check if the email exists in the database
    $sql_check = "SELECT COUNT(*) FROM users WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) {
        die("SQL Error: " . $conn->error);
    }

    $stmt_check->bind_param('s', $email);
    $stmt_check->execute();
    $stmt_check->bind_result($email_exists);
    $stmt_check->fetch();
    $stmt_check->close();

    if (!$email_exists) {
        $errors[] = $translations[$lang]['email_not_found_message']; // Show error message if email not found
    } else {
        // Prepare SQL query to update token
        $sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param('sss', $token_hash, $expiry, $email);
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->affected_rows) {

            $mail = require __DIR__ . '/mailer.php';

            $mail->setFrom('your_email@example.com', 'Your Name');
            $mail->addAddress($email);
            $mail->Subject = $translations[$lang]['reset_email_subject'];

            // Translate the email body
            $reset_link = str_replace(['{email}', '{token}'], [$email, $token], $translations[$lang]['reset_email_message']);
            $mail->Body = "<p>{$reset_link}</p>";

            try {
                $mail->send();
                $success_message = $translations[$lang]['success_message'];
            } catch (Exception $e) {
                $errors[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}

?>

<!-- HTML code -->
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations[$lang]['title']; ?></title>
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
                    <a href="?lang=en">EN</a> | <a href="?lang=ms">BM</a>
                </div>
            </div>
        </div>

        <!-- Display success message -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Display error message -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="forgot-pwd.php" method="post">
            <div class="form-group">
                <input type="email" placeholder="<?php echo $translations[$lang]['enter_email']; ?>" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-btn">
                <input type="submit" value="<?php echo $translations[$lang]['send']; ?>" name="verify" class="btn btn-primary">
            </div>
        </form>
    </div>

</body>


</html>