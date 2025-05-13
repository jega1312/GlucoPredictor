<!-- Login Page -->

<!-- PHP code -->
<?php
session_start();

// Prevent session fixation
session_regenerate_id(true);

// Security Headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === "Admin") {
        header("Location: admin/admin-index.php");
    } else {
        header("Location: dashboard/index.php");
    }
    exit();
}

require_once "database.php";

// CSRF Protection - Generate Token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Language Feature
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default to English
}
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$translations = [
    'en' => [
        'title' => 'Login',
        'email' => 'Email Address',
        'password' => 'Password',
        'login' => 'Login',
        'not_registered' => 'Not registered yet?',
        'register_here' => 'Register here',
        'forgot_password' => 'Forgot Password?',
        'english' => 'EN',
        'malay' => 'BM',
        'missing_at_symbol' => 'Email is missing "@" symbol.',
        'missing_domain' => 'Email is missing domain part.',
        'invalid_tld' => 'Invalid email domain.',
        'password_length' => 'Password must be at least 8 characters long.',
        'password_uppercase' => 'Password must contain at least one uppercase letter.',
        'password_number' => 'Password must contain at least one number.',
        'csrf_error' => 'Invalid CSRF token.',
        'database_error' => 'Database error occurred.',
        'too_many_attempts' => 'Too many failed login attempts. Try again later.',
        'account_not_activated' => 'Account not activated.',
        'email_not_found' => 'Email not found.',
        'password_incorrect' => 'Incorrect password.',
    ],
    'ms' => [
        'title' => 'Log Masuk',
        'email' => 'Alamat Emel',
        'password' => 'Kata Laluan',
        'login' => 'Log Masuk',
        'not_registered' => 'Belum mendaftar lagi?',
        'register_here' => 'Daftar di sini',
        'forgot_password' => 'Lupa Kata Laluan?',
        'english' => 'EN',
        'malay' => 'BM',
        'missing_at_symbol' => 'Emel tidak mempunyai simbol "@"',
        'missing_domain' => 'Emel tidak mempunyai bahagian domain.',
        'invalid_tld' => 'Domain emel tidak sah.',
        'password_length' => 'Kata Laluan harus panjang selembar 8 huruf.',
        'password_uppercase' => 'Kata Laluan mesti mengandungi sekurang-kurangnya satu huruf besar.',
        'password_number' => 'Kata Laluan mesti mengandungi sekurang-kurangnya satu nombor.',
        'csrf_error' => 'Token CSRF tidak sah.',
        'database_error' => 'Ralat pangkalan data berlaku.',
        'too_many_attempts' => 'Terlalu banyak percubaan log masuk gagal. Cuba lagi nanti.',
        'account_not_activated' => 'Akaun tidak diaktifkan.',
        'email_not_found' => 'Emel tidak ditemui.',
        'password_incorrect' => 'Kata laluan tidak betul.',
    ]
];

$lang = $_SESSION['lang'];
$error_message = "";

// Process Login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    // Validate CSRF Token
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error_message = $translations[$lang]['csrf_error'];
    } else {
        // Validate Inputs
        $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
        $password = trim($_POST["password"]);

        if (!$email || empty($password)) {
            $error_message = $translations[$lang]['invalid_credentials'];
        } else {
            // Email Validation
            // Check for missing '@' symbol
            if (strpos($email, '@') === false) {
                $error_message = $translations[$lang]['missing_at_symbol'];
            }
            // Check for a valid domain part
            $domain = substr(strrchr($email, '@'), 1);
            if (empty($domain)) {
                $error_message = $translations[$lang]['missing_domain'];
            }
            // Check for a valid TLD (e.g., .com, .org, .net)
            elseif (!preg_match('/\.(com|org|net|edu)$/', $domain)) {
                $error_message = $translations[$lang]['invalid_tld'];
            }

            // Password Validation
            elseif (strlen($password) < 8) {
                $error_message = $translations[$lang]['password_length'];
            } elseif (!preg_match("/[A-Z]/", $password)) {
                $error_message = $translations[$lang]['password_uppercase'];
            } elseif (!preg_match("/[0-9]/", $password)) {
                $error_message = $translations[$lang]['password_number'];
            } else {
                $stmt = $conn->prepare("SELECT user_id, password, account_activation_hash, role FROM users WHERE email = ?");
                if (!$stmt) {
                    $error_message = $translations[$lang]['database_error'];
                } else {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();

                        if ($user && $user["account_activation_hash"] === NULL) {
                            if (password_verify($password, $user["password"])) {
                                $_SESSION["user"] = "yes";
                                $_SESSION["user_id"] = $user["user_id"];
                                $_SESSION["role"] = $user["role"];

                                if ($user["role"] === "Admin") {
                                    header("Location: admin/admin-index.php");
                                } else {
                                    header("Location: dashboard/index.php");
                                }
                                exit();
                            } else {
                                $error_message = $translations[$lang]['password_incorrect']; // Password incorrect
                            }
                        } else {
                            $error_message = $translations[$lang]['account_not_activated']; // Account not activated
                        }
                    } else {
                        $error_message = $translations[$lang]['email_not_found']; // Email not found
                    }
                }
            }
        }
    }
}
?>

<!-- HTML code -->
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">

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
                <a href="?lang=en"><?php echo $translations[$lang]['english']; ?></a> |
                <a href="?lang=ms"><?php echo $translations[$lang]['malay']; ?></a>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <input type="email" placeholder="<?php echo $translations[$lang]['email']; ?>" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="password" placeholder="<?php echo $translations[$lang]['password']; ?>" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-btn">
                <input type="submit" value="<?php echo $translations[$lang]['login']; ?>" name="login" class="btn btn-primary">
            </div>
        </form>

        <div class="auth-links">
            <p><a href="forgot-pwd.php"><?php echo $translations[$lang]['forgot_password']; ?></a></p>
            <p><?php echo $translations[$lang]['not_registered']; ?> <a href="registration.php"><?php echo $translations[$lang]['register_here']; ?></a></p>
        </div>
    </div>
</body>


</html>