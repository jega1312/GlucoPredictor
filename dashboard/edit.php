<!-- Edit Profile Page -->

<!-- PHP code -->
<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "User") {
    header("Location: /glucopredictor/login.php");
    exit();
}


// Handle language selection
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'en'; // Default to English

// Translation array
$translations = [
    'en' => [
        'home' => 'Home',
        'risk_assessment' => 'Risk Assessment',
        'history' => 'History',
        'health_tips' => 'Health Tips',
        'glucose_tracking' => 'Glucose Tracking',
        'weight_tracking' => 'Weight Tracking',
        'insulin_tracking' => 'Insulin Tracking',
        'logout' => 'Logout',
        'dark_mode' => 'Dark Mode',
        'english' => 'EN',
        'malay' => 'BM',
        'edit_profile' => 'Edit Profile',
        'username' => 'New Username:',
        'username_placeholder' => 'Enter your new username (Optional)',
        'new_password' => 'New Password:',
        'new_password_placeholder' => 'Enter your new password (Optional)',
        'confirm_password' => 'Confirm New Password:',
        'confirm_password_placeholder' => 'Confirm your new password (Optional)',
        'save_changes' => 'Save Changes',
        'delete_account' => 'Delete Account',
        'all_fields_required' => 'All fields are required!',
        'incorrect_password' => 'Incorrect current password!',
        'passwords_not_match' => 'Passwords do not match!',
        'profile_updated' => 'Profile updated successfully!',
        'error_updating_profile' => 'Error updating profile.',
        'account_deleted' => 'Account deleted successfully!',
        'error_deleting_account' => 'Error deleting account.',
        'confirm_delete' => 'Are you sure you want to delete this account? This action cannot be undone.',
        'logout_confirmation' => 'Are you sure you want to log out?',
        'username_length' => 'Username must be at least 4 characters long.',
        'password_fields_required' => 'Both new password and confirm password fields must be filled.',
        'password_length' => 'Password must be at least 8 characters long.',
        'password_uppercase' => 'Password must contain at least one uppercase letter.',
        'password_number' => 'Password must contain at least one number.',
        'error_updating_username' => 'Error updating username.',
        'username_updated' => 'Username updated successfully!',
        'error_updating_password' => 'Error updating password.',
        'password_updated' => 'Password updated successfully!',
        'unauthorized_action' => 'Unauthorized action!',
    ],
    'ms' => [
        'home' => 'Laman Utama',
        'risk_assessment' => 'Penilaian Risiko',
        'history' => 'Sejarah',
        'health_tips' => 'Tip Kesihatan',
        'glucose_tracking' => 'Penjejakan Glukosa',
        'weight_tracking' => 'Penjejakan Berat',
        'insulin_tracking' => 'Penjejakan Insulin',
        'logout' => 'Log Keluar',
        'dark_mode' => 'Mod Gelap',
        'english' => 'EN',
        'malay' => 'BM',
        'edit_profile' => 'Kemaskini Profil',
        'username' => 'Nama Pengguna Baru:',
        'username_placeholder' => 'Masukkan nama pengguna baru anda (Pilihan)',
        'new_password' => 'Kata Laluan Baru:',
        'new_password_placeholder' => 'Masukkan kata laluan baru anda (Pilihan)',
        'confirm_password' => 'Sahkan Kata Laluan Baru:',
        'confirm_password_placeholder' => 'Sahkan kata laluan baru anda (Pilihan)',
        'save_changes' => 'Simpan Perubahan',
        'delete_account' => 'Padam Akaun',
        'all_fields_required' => 'Semua medan diperlukan!',
        'incorrect_password' => 'Kata laluan semasa tidak betul!',
        'passwords_not_match' => 'Kata laluan tidak sepadan!',
        'profile_updated' => 'Profil berjaya dikemaskini!',
        'error_updating_profile' => 'Ralat mengemaskini profil.',
        'account_deleted' => 'Akaun berjaya dipadam!',
        'error_deleting_account' => 'Ralat memadam akaun.',
        'confirm_delete' => 'Adakah anda pasti mahu memadam akaun ini? Tindakan ini tidak boleh dikembalikan.',
        'logout_confirmation' => 'Adakah anda pasti mahu log keluar?',
        'username_length' => 'Nama pengguna mesti mempunyai sekurang-kurangnya 4 aksara.',
        'password_fields_required' => 'Kedua-dua kata laluan baru dan medan pengesahan kata laluan mesti diisi.',
        'password_length' => 'Kata laluan mesti mempunyai sekurang-kurangnya 8 aksara.',
        'password_uppercase' => 'Kata laluan mesti mengandungi sekurang-kurangnya satu huruf besar.',
        'password_number' => 'Kata laluan mesti mengandungi sekurang-kurangnya satu angka.',
        'error_updating_username' => 'Ralat mengemaskini nama pengguna.',
        'error_updating_password' => 'Ralat mengemaskini kata laluan.',
        'unauthorized_action' => 'Tindakan tidak dibenarkan!',
    ],
];

// Database connection
include("../database.php");

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT username, email, password FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$username = $user["username"] ?? "";
$email_db = $user["email"] ?? "";
$password_db = $user["password"] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_account'])) {
        // Handle Account Deletion
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                session_destroy();
                header("Location: edit.php"); // Redirect after account deletion
                exit();
            } else {
                $errors[] = $translations[$lang]['error_deleting_account'];
            }
        } else {
            $errors[] = $translations[$lang]['unauthorized_action'];
        }
    } else {
        // Validate Inputs
        $username = trim($_POST['username']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Ensure at least one field is filled
        if (empty($username) && empty($new_password) && empty($confirm_password)) {
            $errors[] = $translations[$lang]['all_fields_required'];
        }

        // Validate username only if provided
        if (!empty($username) && strlen($username) < 4) {
            $errors[] = $translations[$lang]['username_length'];
        }

        // Ensure password fields are either BOTH filled or BOTH empty
        if (!empty($new_password) || !empty($confirm_password)) {
            if (empty($new_password) || empty($confirm_password)) {
                $errors[] = $translations[$lang]['password_fields_required'];
            } elseif (strlen($new_password) < 8) {
                $errors[] = $translations[$lang]['password_length'];
            } elseif (!preg_match("/[A-Z]/", $new_password)) {
                $errors[] = $translations[$lang]['password_uppercase'];
            } elseif (!preg_match("/[0-9]/", $new_password)) {
                $errors[] = $translations[$lang]['password_number'];
            } elseif ($new_password !== $confirm_password) {
                $errors[] = $translations[$lang]['passwords_not_match'];
            }
        }

        // If No Errors, Update the User Profile
        if (empty($errors)) {
            $user_id = $_SESSION['user_id']; // Assume user is logged in
            $username_updated = false;
            $password_updated = false;

            // **Update username only if provided (not empty)**
            if (!empty($username)) {
                $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
                $stmt->bind_param("si", $username, $user_id);
                if ($stmt->execute()) {
                    $username_updated = true;
                } else {
                    $errors[] = $translations[$lang]['error_updating_username'] . $stmt->error;
                }
            }

            // **Update password only if both fields are filled**
            if (!empty($new_password) && !empty($confirm_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                if ($stmt->execute()) {
                    $password_updated = true;
                } else {
                    $errors[] = $translations[$lang]['error_updating_password'] . $stmt->error;
                }
            }

            // Build success message
            $success = '';
            if ($username_updated) {
                $success .= $translations[$lang]['username_updated'];
            }
            if ($password_updated) {
                $success .= $translations[$lang]['password_updated'];
            }
        }
    }
}


// Close database connection
$conn->close();
?>


<!-- HTML code -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations[$lang]['edit_profile']; ?></title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" href="images/logo-favicon.png">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
</head>

<body>
    <nav>
        <div class="name">
            <a href="index.php">
                <p class="name"><span>Gluco</span>Predictor</p>
            </a>
        </div>

        <div class="menu-items">
            <ul class="nav-links">
                <li><a href="index.php">
                        <i class="uil uil-estate"></i>
                        <p class="link-name"><?php echo $translations[$lang]['home']; ?></p>
                    </a></li>

                <li><a href="form.php">
                        <i class="uil uil-file-medical-alt"></i>
                        <p class="link-name"><?php echo $translations[$lang]['risk_assessment']; ?></p>
                    </a></li>

                <li><a href="tips.php">
                        <i class="uil uil-heart-medical"></i>
                        <p class="link-name"><?php echo $translations[$lang]['health_tips']; ?></p>
                    </a></li>

                <li><a href="history.php">
                        <i class="uil uil-history"></i>
                        <p class="link-name"><?php echo $translations[$lang]['history']; ?></p>
                    </a></li>

                <li><a href="glucose.php">
                        <i class="uil uil-tear"></i>
                        <p class="link-name"><?php echo $translations[$lang]['glucose_tracking']; ?></p>
                    </a></li>

                <li><a href="weight.php">
                        <i class="uil uil-weight"></i>
                        <p class="link-name"><?php echo $translations[$lang]['weight_tracking']; ?></p>
                    </a></li>

                <li><a href="insulin.php">
                        <i class="uil uil-syringe"></i>
                        <p class="link-name"><?php echo $translations[$lang]['insulin_tracking']; ?></p>
                    </a></li>

            </ul>


            <div class="bottom-menu">
                <ul class="logout-mode">
                    <li><a href="logout.php" id="logoutBtn">
                            <i class="uil uil-sign-out-alt"></i>
                            <p class="link-name"><?php echo $translations[$lang]['logout']; ?></p>
                        </a></li>
                </ul>

                <ul class="dark-mode">
                    <li class="mode">
                        <a href="#">
                            <i class="uil uil-moon"></i>
                            <span class="link-name"><?php echo $translations[$lang]['dark_mode']; ?></span>
                        </a>
                        <div class="mode-toggle">
                            <span class="switch"></span>
                        </div>
                    </li>
                </ul>


            </div>
    </nav>

    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <div class="profile-container">
                <img src="images/profile.png" alt="Profile Picture" class="profile-pic" id="profileToggle">
                <ul class="dropdown-menu" id="dropdownMenu">

                    <li>
                        <div class="text-center mt-2">
                            <a href="?lang=en"><?php echo $translations[$lang]['english']; ?></a> |
                            <a href="?lang=ms"><?php echo $translations[$lang]['malay']; ?></a>
                        </div>
                    </li>
                    <li><a href="logout.php" id="out"><?php echo $translations[$lang]['logout']; ?></a></li>
                </ul>
            </div>

        </div>

        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-edit-alt"></i>
                    <span class="text"><?php echo $translations[$lang]['edit_profile']; ?></span>
                </div>

                <div class="edit-profile-container">
                    <form action="edit.php" method="POST">

                        <?php if (!empty($success)): ?>
                            <p class="alert-success"><?php echo $success; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <?php foreach ($errors as $error): ?>
                                <p class="alert-danger"><?php echo $error; ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Name Field -->
                        <div class="form-group">
                            <label for="name"><?php echo $translations[$lang]['username']; ?></label>
                            <input type="text" id="username" name="username" placeholder="<?php echo $translations[$lang]['username_placeholder']; ?>">
                        </div>

                        <div class="form-group">
                            <label for="new_password"><?php echo $translations[$lang]['new_password']; ?></label>
                            <input type="password" id="new_password" name="new_password" placeholder="<?php echo $translations[$lang]['new_password_placeholder']; ?>">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password"><?php echo $translations[$lang]['confirm_password']; ?></label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="<?php echo $translations[$lang]['confirm_password_placeholder']; ?>">
                        </div>


                        <!-- Submit Button -->
                        <div class="form-group">
                            <form action="edit.php" method="POST">
                                <button type="submit" id="btn-edit"><?php echo $translations[$lang]['save_changes']; ?></button>
                            </form>
                        </div>

                        <!-- Delete Account Button with Confirmation Alert -->
                        <div class="form-group delete-account">
                            <form action="edit.php" method="POST" onsubmit="return confirmDelete();">
                                <button type="submit" name="delete_account" id="btn-delete"><?php echo $translations[$lang]['delete_account']; ?></button>
                            </form>
                        </div>

                    </form>



                </div>
    </section>


    <!-- JavaScript code -->
    <script>
        function confirmDelete() {
            return confirm("<?php echo $translations[$lang]['confirm_delete']; ?>");
        }

        const body = document.querySelector("body");
        const modeToggle = document.querySelector(".mode-toggle");

        // Check localStorage for dark mode preference
        if (localStorage.getItem("darkMode") === "enabled") {
            body.classList.add("dark");
        }

        // Toggle dark mode on button click
        modeToggle.addEventListener("click", () => {
            body.classList.toggle("dark");

            // Save preference in localStorage
            if (body.classList.contains("dark")) {
                localStorage.setItem("darkMode", "enabled");
            } else {
                localStorage.setItem("darkMode", "disabled");
            }
        });

        function attachLogoutConfirmation(id) {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener("click", function(e) {
                    e.preventDefault();

                    var isConfirmed = confirm("<?php echo $translations[$lang]['logout_confirmation']; ?>");

                    if (isConfirmed) {
                        localStorage.removeItem('activeTab');
                        window.location.href = "logout.php";
                    }
                });
            }
        }

        // Attach to both buttons/links
        attachLogoutConfirmation("logoutBtn");
        attachLogoutConfirmation("out");

        sidebar = body.querySelector("nav");
        sidebarToggle = body.querySelector(".sidebar-toggle");


        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });

        // Profile picture and dropdown menu elements
        const profileToggle = document.getElementById('profileToggle');
        const dropdownMenu = document.getElementById('dropdownMenu');

        // Toggle the dropdown menu
        profileToggle.addEventListener('click', () => {
            // Visibility of the dropdown menu
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        // Close the dropdown menu
        document.addEventListener('click', (event) => {
            if (!profileToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.style.display = 'none';
            }
        });
    </script>


</body>



</html>