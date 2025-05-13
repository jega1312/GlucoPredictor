<!-- Registration Page -->

<!-- PHP code -->
<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Strict'
]);

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

require_once "database.php";

// Language Support
$lang = isset($_GET['lang']) && $_GET['lang'] == 'ms' ? 'ms' : 'en';
$translations = [
    'en' => [
        'english' => 'EN',
        'malay' => 'BM',
        'title' => 'Registration',
        'register' => 'Register',
        'username' => 'Username',
        'email' => 'Email Address',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'already_registered' => 'Already registered?',
        'login_here' => 'Login here',
        'all_fields_required' => 'All fields are required.',
        'invalid_email' => 'Invalid email format.',
        'missing_at_symbol' => 'Missing "@" symbol in email.',
        'missing_domain' => 'Missing domain after "@" in email.',
        'invalid_tld' => 'Invalid email domain.',
        'password_requirements' => 'Password must be at least 8 characters, contain one uppercase letter and one number.',
        'password_mismatch' => 'Passwords do not match.',
        'email_exists' => 'Email address already exists!',
        'registration_success' => 'You have successfully registered. Please check your email, including spam and junk folders, to activate your account.',
        'registration_error' => 'Something went wrong. Please try again.',
        'activation_subject' => 'GlucoPredictor Account Activation',
        'activation_message' => 'Click <a href="http://localhost/glucopredictor/activate-account-en.php?email={email}&token={token}">here</a> to activate your account.'
    ],
    'ms' => [
        'english' => 'EN',
        'malay' => 'BM',
        'title' => 'Pendaftaran',
        'register' => 'Daftar',
        'username' => 'Nama Pengguna',
        'email' => 'Alamat Emel',
        'password' => 'Kata Laluan',
        'confirm_password' => 'Sahkan Kata Laluan',
        'already_registered' => 'Sudah berdaftar?',
        'login_here' => 'Log Masuk di sini',
        'all_fields_required' => 'Semua ruangan harus diisi.',
        'invalid_email' => 'Format emel tidak sah.',
        'missing_at_symbol' => 'Simbol "@" hilang dalam emel.',
        'missing_domain' => 'Domain hilang selepas "@" dalam emel.',
        'invalid_tld' => 'Domain emel tidak sah.',
        'password_requirements' => 'Kata laluan mesti sekurang-kurangnya 8 aksara, mengandungi satu huruf besar dan satu nombor.',
        'password_mismatch' => 'Kata laluan tidak sepadan.',
        'email_exists' => 'Alamat emel telah didaftarkan!',
        'registration_success' => 'Anda telah berjaya mendaftar. Sila semak emel anda, termasuk penyata spam dan sampah, untuk mengaktifkan akaun anda.',
        'registration_error' => 'Ralat berlaku. Sila cuba lagi.',
        'activation_subject' => 'Pengaktifan Akaun GlucoPredictor',
        'activation_message' => 'Klik di <a href="http://localhost/glucopredictor/activate-account-bm.php?email={email}&token={token}"> sini</a> untuk mengaktifkan akaun anda.'
    ]
];

$errors = [];
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    $userName = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $passwordRepeat = $_POST["repeat-pwd"];

    // Validate input fields
    if (empty($userName) || empty($email) || empty($password) || empty($passwordRepeat)) {
        $errors[] = $translations[$lang]['all_fields_required'];
    }
    // Basic email validation using filter_var()
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $translations[$lang]['invalid_email'];
    } else {
        // Check for missing '@' symbol
        if (strpos($email, '@') === false) {
            $errors[] = $translations[$lang]['missing_at_symbol'];
        }
        // Check if domain is missing after '@'
        $domain = substr(strrchr($email, '@'), 1);
        if (empty($domain)) {
            $errors[] = $translations[$lang]['missing_domain'];
        }
        // Check if domain has a valid TLD (e.g., .com, .org)
        if (!preg_match('/\.(com|org|net|edu)$/', $domain)) {
            $errors[] = $translations[$lang]['invalid_tld'];
        }
    }
    if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
        $errors[] = $translations[$lang]['password_requirements'];
    }
    if ($password !== $passwordRepeat) {
        $errors[] = $translations[$lang]['password_mismatch'];
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = $translations[$lang]['email_exists'];
    }
    $stmt->close();

    $activation_token = bin2hex(random_bytes(16));
    $activation_token_hash = hash('sha256', $activation_token);

    // If no errors, proceed with registration
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        if (!$password_hash) {
            $errors[] = $translations[$lang]['registration_error'];
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, account_activation_hash) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $userName, $email, $password_hash, $activation_token_hash);

            if ($stmt->execute()) {

                $mail = require __DIR__ . '/mailer.php';

                $mail->setFrom('your_email@example.com', 'Your Name');
                $mail->addAddress($_POST['email']);
                $mail->Subject = $translations[$lang]['activation_subject'];
                $mail->Body = str_replace(
                    ['{email}', '{token}'],
                    [$email, $activation_token],
                    $translations[$lang]['activation_message']
                );

                //         $mail->Body = <<<END
                // <p>Click <a href="http://localhost/glucopredictor/activate-account.php?email={$email}&token={$activation_token}">here</a> to activate your account.</p>
                // END;

                try {
                    $mail->send();
                    $success_message = $translations[$lang]['registration_success'];
                } catch (Exception $e) {
                    $errors[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    exit;
                }

                $success_message = $translations[$lang]['registration_success'];
            } else {
                $errors[] = $translations[$lang]['registration_error'];
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>


<!-- HTML code -->
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations[$lang]['title']; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="dashboard/images/logo-favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="name-lang">
            <header><span>Gluco</span>Predictor</header>

            <!-- Centered Language Switcher -->
            <div class="text-center mt-2">
                <a href="?lang=en"><?php echo $translations[$lang]['english']; ?></a> |
                <a href="?lang=ms"><?php echo $translations[$lang]['malay']; ?></a>
            </div>
        </div>

        <!-- Display error messages -->
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="registration.php?lang=<?php echo $lang; ?>" method="post">
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="<?php echo $translations[$lang]['username']; ?>" id="username" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="<?php echo $translations[$lang]['email']; ?>" id="email" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="<?php echo $translations[$lang]['password']; ?>" id="password" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="repeat-pwd" placeholder="<?php echo $translations[$lang]['confirm_password']; ?>" id="repeat-pwd" required>
            </div>
            <div class="form-btn">
                <input type="submit" class="btn btn-primary" value="<?php echo $translations[$lang]['register']; ?>" name="submit">
            </div>
        </form>
        <div class="auth-links">
            <p><?php echo $translations[$lang]['already_registered']; ?> <a href="login.php?lang=<?php echo $lang; ?>"><?php echo $translations[$lang]['login_here']; ?></a></p>
        </div>
    </div>
</body>


</html>