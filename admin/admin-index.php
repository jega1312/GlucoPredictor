<!-- Admin Dashboard Page -->

<!-- PHP code -->
<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
  header("Location: ../login.php");
  exit();
}

//Translation
// Handle language selection
// Check if language is set through GET, otherwise default to English
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ms'])) {
  $_SESSION['lang'] = $_GET['lang'];
}

// Default language if none is set
$lang = $_SESSION['lang'] ?? 'en';

$translations = [
  'en' => [
    'title' => 'Admin Dashboard',
    'delete_own_account' => "You cannot delete your own admin account.",
    'user_deleted' => "User deleted successfully.",
    'delete_error' => "Error deleting user: ",
    'fields_required' => 'All fields are required.',
    'password_fields_required' => 'Both password fields are required.',
    'password_min_length' => 'Password must be at least 8 characters.',
    'password_uppercase' => 'Password must contain at least one uppercase letter.',
    'password_number' => 'Password must contain at least one number.',
    'passwords_do_not_match' => 'Passwords do not match.',
    'username_update_success' => 'Username successfully updated.',
    'username_update_error' => 'Error updating username: ',
    'password_update_success' => 'Password successfully updated.',
    'password_update_error' => 'Error updating password: ',
    'invalid_email_format' => 'Invalid email format.',
    'email_no_at_symbol' => 'Email must contain "@" symbol.',
    'email_invalid_domain' => 'Email must end with a valid domain like .com, .org, .net, or .edu.',
    'email_dns_error' => 'Invalid email domain.',
    'email_in_use' => 'Email is already in use by another user.',
    'invalid_role' => 'Invalid role selected.',
    'user_update_success' => 'User updated successfully.',
    'user_update_error' => 'Error updating user: ',
    'user_label' => 'Users',
    'user_role' => 'User',
    'stats_label' => 'Stats',
    'profile_label' => 'Profile',
    'logout_label' => 'Logout',
    'dark_mode' => 'Dark Mode',
    'logout_confirm' => 'Are you sure you want to logout?',
    'user_management' => 'User Management',
    'user_id' => 'User ID',
    'username' => 'Username',
    'email' => 'Email',
    'role' => 'Role',
    'actions' => 'Actions',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'save_changes' => 'Save Changes',
    'cancel' => 'Cancel',
    'previous' => 'Previous',
    'next' => 'Next',
    'no_users_found' => 'No users found.',
    'User' => 'User',
    'Admin' => 'Admin',
    'system_stats' => 'System Stats',
    'total_users' => 'Total Users',
    'admins' => 'Admins',
    'active_users' => 'Active Users',
    'user_role_distribution' => 'User Role Distribution',
    'user_count' => 'User Count',
    'edit_admin_profile' => 'Edit Current Admin Profile',
    'new_username' => 'New Username',
    'new_username_placeholder' => 'Enter new username (Optional)',
    'new_password_placeholder' => 'Enter new password (Optional)',
    'confirm_password_placeholder' => 'Confirm new password (Optional)',
    'new_password' => 'New Password',
    'confirm_password' => 'Confirm Password',
    'save' => 'Save',
  ],

  'ms' => [
    'title' => 'Papan Pemuka Admin',
    'delete_own_account' => "Anda tidak boleh memadam akaun admin anda sendiri.",
    'user_deleted' => "Pengguna berjaya dipadam.",
    'delete_error' => "Ralat memadam pengguna: ",
    'fields_required' => 'Semua medan adalah wajib.',
    'password_fields_required' => 'Kedua-dua medan kata laluan adalah wajib.',
    'password_min_length' => 'Kata laluan mesti sekurang-kurangnya 8 aksara.',
    'password_uppercase' => 'Kata laluan mesti mengandungi sekurang-kurangnya satu huruf besar.',
    'password_number' => 'Kata laluan mesti mengandungi sekurang-kurangnya satu nombor.',
    'passwords_do_not_match' => 'Kata laluan tidak sepadan.',
    'username_update_success' => 'Nama pengguna berjaya dikemas kini.',
    'username_update_error' => 'Ralat mengemas kini nama pengguna: ',
    'password_update_success' => 'Kata laluan berjaya dikemas kini.',
    'password_update_error' => 'Ralat mengemas kini kata laluan: ',
    'invalid_email_format' => 'Format emel tidak sah.',
    'email_no_at_symbol' => 'Emel mesti mengandungi simbol "@"',
    'email_invalid_domain' => 'Emel mesti diakhiri dengan domain yang sah seperti .com, .org, .net, atau .edu.',
    'email_dns_error' => 'Domain emel tidak sah.',
    'email_in_use' => 'Emel sudah digunakan oleh pengguna lain.',
    'invalid_role' => 'Peranan yang dipilih tidak sah.',
    'user_update_success' => 'Pengguna berjaya dikemas kini.',
    'user_update_error' => 'Ralat mengemas kini pengguna: ',
    'user_label' => 'Pengguna',
    'user_role' => 'Pengguna',
    'stats_label' => 'Statistik',
    'profile_label' => 'Profil',
    'logout_label' => 'Log Keluar',
    'dark_mode' => 'Mod Gelap',
    'logout_confirm' => 'Adakah anda pasti ingin log keluar?',
    'user_management' => 'Pengurusan Pengguna',
    'user_id' => 'ID Pengguna',
    'username' => 'Nama Pengguna',
    'email' => 'Emel',
    'role' => 'Peranan',
    'actions' => 'Tindakan',
    'edit' => 'Kemaskini',
    'delete' => 'Padam',
    'save_changes' => 'Simpan Perubahan',
    'cancel' => 'Batalkan',
    'previous' => 'Sebelum',
    'next' => 'Seterusnya',
    'no_users_found' => 'Tiada pengguna dijumpai.',
    'User' => 'Pengguna',
    'Admin' => 'Admin',
    'system_stats' => 'Statistik Sistem',
    'total_users' => 'Jumlah Pengguna',
    'admins' => 'Admin',
    'active_users' => 'Pengguna Aktif',
    'user_role_distribution' => 'Pengagihan Peranan Pengguna',
    'user_count' => 'Kiraan Pengguna',
    'edit_admin_profile' => 'Kemaskini Profil Admin Semasa',
    'new_username' => 'Nama Pengguna Baru',
    'new_username_placeholder' => 'Masukkan nama pengguna baru (Pilihan)',
    'new_password_placeholder' => 'Masukkan kata laluan baru (Pilihan)',
    'confirm_password_placeholder' => 'Sahkan kata laluan baru (Pilihan)',
    'new_password' => 'Kata Laluan Baru',
    'confirm_password' => 'Sahkan Kata Laluan',
    'save' => 'Simpan',
  ],
];



// Database connection
include("../database.php");

$user_id = $_SESSION['user_id'];

// Fetch admin data
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


// Get total users
$sql = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_users = $row['total'];

// Get total admins
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'Admin'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_admins = $row['total'];

// Get total users with role 'User'
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'User' AND (account_activation_hash IS NULL OR account_activation_hash = '')";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$active_users = $row['total'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $success = '';
  $errors = [];

  // === DELETE USER ===
  if (!empty($_POST['delete_user_id'])) {
    $delete_user_id = intval($_POST['delete_user_id']);

    if ($delete_user_id === $user_id) {
      $errors[] = $translations[$lang]['delete_own_account'];
    } else {
      $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
      $stmt->bind_param("i", $delete_user_id);
      if ($stmt->execute()) {
        $success = $translations[$lang]['user_deleted'];
      } else {
        $errors[] = $translations[$lang]['delete_error'] . $stmt->error;
      }
      $stmt->close();
    }
  }

  // === UPDATE USERNAME/PASSWORD ===
  if (
    (!isset($_POST['action']) || $_POST['action'] === 'edit_profile') &&
    (isset($_POST['username']) || isset($_POST['new_password']))
  ) {
    $username = trim($_POST['username'] ?? '');
    $user_id = (int) $_SESSION['user_id'];
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // === Update Username ===
    if (!empty($username)) {
      $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
      $stmt->bind_param("si", $username, $user_id);
      if ($stmt->execute()) {
        $success .= $translations[$lang]['username_update_success'];
      } else {
        $errors[] = $translations[$lang]['username_update_error'] . $stmt->error;
      }
      $stmt->close();
    }

    // === Password Validations ===
    if (!empty($new_password) || !empty($confirm_password)) {
      if ($new_password === '' || $confirm_password === '') {
        $errors[] = $translations[$lang]['password_fields_required'];
      } elseif (strlen($new_password) < 8) {
        $errors[] = $translations[$lang]['password_min_length'];
      } elseif (!preg_match("/[A-Z]/", $new_password)) {
        $errors[] = $translations[$lang]['password_uppercase'];
      } elseif (!preg_match("/[0-9]/", $new_password)) {
        $errors[] = $translations[$lang]['password_number'];
      } elseif ($new_password !== $confirm_password) {
        $errors[] = $translations[$lang]['passwords_do_not_match'];
      }

      if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        if ($stmt->execute()) {
          $success .= ($success ? "\\n" : "") . $translations[$lang]['password_update_success'];
        } else {
          $errors[] = $translations[$lang]['password_update_error'] . $stmt->error;
        }
        $stmt->close();
      }
    }

    if ($username === '' && $new_password === '' && $confirm_password === '') {
      $errors[] = $translations[$lang]['fields_required'];
    }
  }


  // === EDIT USER (username, email, role) ===
  if (!empty($_POST['action']) && $_POST['action'] === 'edit_user') {
    $edit_user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $role = strtolower(trim($_POST['role']));

    // Email Validations
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = $translations[$lang]['invalid_email_format'];
    } elseif (strpos($email, '@') === false) {
      $errors[] = $translations[$lang]['email_no_at_symbol'];
    } else {
      $email_parts = explode('@', $email);
      if (count($email_parts) !== 2 || empty($email_parts[1])) {
        $errors[] = $translations[$lang]['email_dns_error'];
      } else {
        $domain = strtolower($email_parts[1]);
        if (!preg_match('/\.(com|org|net|edu)$/', $domain)) {
          $errors[] = $translations[$lang]['email_invalid_domain'];
        } elseif (!checkdnsrr($domain, 'MX')) {
          $errors[] = $translations[$lang]['email_dns_error'];
        } else {
          $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
          $stmt->bind_param("si", $email, $edit_user_id);
          $stmt->execute();
          $stmt->store_result();
          if ($stmt->num_rows > 0) {
            $errors[] = $translations[$lang]['email_in_use'];
          }
          $stmt->close();
        }
      }
    }

    // Role Validation
    if (!in_array($role, ['user', 'admin'])) {
      $errors[] = $translations[$lang]['invalid_role'];
    }

    if (empty($errors)) {
      $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?");
      $stmt->bind_param("sssi", $username, $email, $role, $edit_user_id);
      if ($stmt->execute()) {
        $success = $translations[$lang]['user_update_success'];
      } else {
        $errors[] = $translations[$lang]['user_update_error'] . $stmt->error;
      }
      $stmt->close();
    }
  }

  // === DISPLAY ALERTS ===
  if (!empty($errors)) {
    $error_msg = htmlspecialchars(implode("\\n", $errors), ENT_QUOTES);
    echo "<script>alert('$error_msg');</script>";
  } elseif (!empty($success)) {
    $success_msg = htmlspecialchars($success, ENT_QUOTES);
    echo "<script>alert('$success_msg');</script>";
  }
}


?>


<!-- HTML code -->
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <title><?php echo $translations[$lang]['title']; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
  <link rel="shortcut icon" href="../dashboard/images/logo-favicon.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .navbar-brand {
      font-size: 1.75rem;
      font-weight: bold;
      color: #fff;
    }

    .navbar-brand span {
      color: #004aad;
    }

    .tab-content>.tab-pane {
      display: none;
    }

    .tab-content>.active {
      display: block;
    }

    .tab-pane h5 {
      padding-bottom: 10px;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-5">
    <span class="navbar-brand"><span>Gluco</span>Predictor</span>
    <div class="ms-auto d-flex align-items-center gap-2">

      <!-- Language Toggle Button -->
      <?php
      // Set language toggle values
      $toggleLang = $lang === 'en' ? 'ms' : 'en';
      $toggleText = $lang === 'en' ? 'BM' : 'EN';
      ?>
      <a href="?lang=<?php echo $toggleLang; ?>" class="btn btn-outline-primary">
        <?php echo $toggleText; ?>
      </a>


      <!-- Dark Mode Toggle -->
      <button id="darkModeToggle" class="btn btn-outline-light">
        <?php echo $translations[$lang]['dark_mode']; ?>
      </button>

      <!-- Logout Button -->
      <a href="../dashboard/logout.php" class="btn btn-outline-danger"
        onclick="if(confirm('<?php echo $translations[$lang]['logout_confirm']; ?>')) { localStorage.removeItem('activeTab'); } else { return false; }">
        <?php echo $translations[$lang]['logout_label']; ?>
      </a>

    </div>
  </nav>



  <div class="container mt-4">
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="dashboardTabs">
      <li class="nav-item">
        <a class="nav-link active" href="#" data-tab="usersTab"><?php echo $translations[$lang]['user_label']; ?></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-tab="statsTab"><?php echo $translations[$lang]['stats_label']; ?></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-tab="profileTab"><?php echo $translations[$lang]['profile_label']; ?></a>
      </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content mt-3">
      <div id="usersTab" class="tab-pane active">
        <h5><?php echo $translations[$lang]['user_management']; ?></h5>
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead class="table-dark" style="text-align: center;">
              <tr>
                <th>No.</th>
                <th><?php echo $translations[$lang]['user_id']; ?></th>
                <th><?php echo $translations[$lang]['username']; ?></th>
                <th><?php echo $translations[$lang]['email']; ?></th>
                <th><?php echo $translations[$lang]['role']; ?></th>
                <th><?php echo $translations[$lang]['actions']; ?></th>
              </tr>
            </thead>
            <tbody>

              <?php
              // Include database connection at the start
              include '../database.php';

              // Pagination setup
              $limit = 10; // Number of users per page
              $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
              $offset = ($page - 1) * $limit;

              // Total number of users
              $totalUsersResult = $conn->query("SELECT COUNT(*) AS total FROM users");
              $totalUsers = $totalUsersResult->fetch_assoc()['total'];
              $totalPages = ceil($totalUsers / $limit);

              // Fetch users for current page
              $sql = "SELECT user_id, username, email, role FROM users LIMIT $limit OFFSET $offset";
              $result = $conn->query($sql);

              $counter = ($page - 1) * $limit + 1; // Start the counter based on the page number and limit

              if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
              ?>
                  <tr style="text-align: center;">
                    <td><?= $counter++ ?>.</td> <!-- Added period after the counter -->
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($translations[$lang][$row['role']]) ?></td>
                    <td style="text-align: center;">
                      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['user_id'] ?>"><?php echo $translations[$lang]['edit']; ?></button>
                      <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['user_id'] ?>)"><?php echo $translations[$lang]['delete']; ?></button>
                    </td>
                  </tr>

                  <!-- Edit Modal -->
                  <div class="modal fade" id="editModal<?= $row['user_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['user_id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form action="admin-index.php" method="POST">
                          <!-- Hidden field to indicate this is an edit action -->
                          <input type="hidden" name="action" value="edit_user">

                          <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel<?= $row['user_id'] ?>">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                            <div class="mb-3">
                              <label class="form-label"><?php echo $translations[$lang]['username']; ?></label>
                              <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']) ?>" required>
                            </div>
                            <div class="mb-3">
                              <label class="form-label"><?php echo $translations[$lang]['email']; ?></label>
                              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required>
                            </div>
                            <div class="mb-3">
                              <label class="form-label"><?php echo $translations[$lang]['role']; ?></label>
                              <select name="role" class="form-select">
                                <option value="user" <?= $row['role'] === 'User' ? 'selected' : '' ?>><?php echo $translations[$lang]['user_role']; ?></option>
                                <option value="admin" <?= $row['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                              </select>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-success"><?php echo $translations[$lang]['save_changes']; ?></button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $translations[$lang]['cancel']; ?></button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php
                endwhile;
              else:
                ?>
                <tr>
                  <td colspan="5" class="text-center"><?php echo $translations[$lang]['no_users_found']; ?></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>

          <!-- Pagination Controls -->
          <nav>
            <ul class="pagination justify-content-center">
              <?php if ($page > 1): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?= $page - 1 ?>"><?php echo $translations[$lang]['previous']; ?></a>
                </li>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                  <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>

              <?php if ($page < $totalPages): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?= $page + 1 ?>"><?php echo $translations[$lang]['next']; ?></a>
                </li>
              <?php endif; ?>
            </ul>
          </nav>

        </div>
      </div>

      <!-- Stats Tab -->
      <div id="statsTab" class="tab-pane">
        <h5><?php echo $translations[$lang]['system_stats']; ?></h5>
        <div class="row">
          <div class="col-md-4">
            <div class="card text-bg-primary mb-3">
              <div class="card-body">
                <h6 class="card-title"><?php echo $translations[$lang]['total_users']; ?></h6>
                <p class="card-text"><?php echo $total_users; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card text-bg-success mb-3">
              <div class="card-body">
                <h6 class="card-title"><?php echo $translations[$lang]['admins']; ?></h6>
                <p class="card-text"><?php echo $total_admins; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card text-bg-warning mb-3">
              <div class="card-body">
                <h6 class="card-title"><?php echo $translations[$lang]['active_users']; ?></h6>
                <p class="card-text"><?php echo $active_users; ?></p>
              </div>
            </div>
          </div>
          <div class="card mt-4">
            <div class="card-body text-center">
              <h5 class="card-title"><?php echo $translations[$lang]['user_role_distribution']; ?></h5>
              <div class="d-flex justify-content-center">
                <canvas id="userChart" style="max-height: 250px;"></canvas>
              </div>
            </div>
          </div>


        </div>
      </div>

      <!-- Profile Tab -->
      <div id="profileTab" class="tab-pane">
        <h5><?php echo $translations[$lang]['edit_admin_profile']; ?></h5>
        <form action="admin-index.php" method="post">
          <div class="mb-3">
            <label class="form-label" style="font-weight: bold;"><?php echo $translations[$lang]['new_username']; ?></label>
            <input type="text" name="username" class="form-control" placeholder="<?php echo $translations[$lang]['new_username_placeholder']; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-weight: bold;"><?php echo $translations[$lang]['new_password']; ?></label>
            <input type="password" name="new_password" class="form-control" placeholder="<?php echo $translations[$lang]['new_password_placeholder']; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-weight: bold;"><?php echo $translations[$lang]['confirm_password']; ?></label>
            <input type="password" name="confirm_password" class="form-control" placeholder="<?php echo $translations[$lang]['confirm_password_placeholder']; ?>">
          </div>
          <button type="submit" class="btn btn-primary"><?php echo $translations[$lang]['save']; ?></button>
        </form>
      </div>
    </div>
  </div>



  <!-- JavaScript code -->
  <script>
    // Tab switching logic with localStorage
    document.querySelectorAll('[data-tab]').forEach(tab => {
      tab.addEventListener('click', function(e) {
        e.preventDefault();

        // Remove active classes
        document.querySelectorAll('[data-tab]').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

        // Add active classes to clicked tab and corresponding pane
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');

        // Save active tab to localStorage
        localStorage.setItem('activeTab', tab.dataset.tab);
      });
    });

    // Restore the last active tab on page load
    window.addEventListener('DOMContentLoaded', () => {
      const savedTab = localStorage.getItem('activeTab');
      if (savedTab) {
        // Remove existing actives
        document.querySelectorAll('[data-tab]').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

        // Activate saved tab
        const activeTab = document.querySelector(`[data-tab="${savedTab}"]`);
        const activePane = document.getElementById(savedTab);
        if (activeTab && activePane) {
          activeTab.classList.add('active');
          activePane.classList.add('active');
        }
      }
    });

    function confirmDelete(userId) {
      if (confirm('Are you sure you want to delete this user?')) {
        // Create a hidden form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ''; // same page

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_user_id';
        input.value = userId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
      }
    }

    // Function to open the modal
    function openEditModal(userId) {
      // Get the modal element by its ID
      var modal = new bootstrap.Modal(document.getElementById('editModal' + userId), {
        keyboard: false
      });

      // Show the modal
      modal.show();
    }

    // Apply theme on page load
    document.addEventListener('DOMContentLoaded', () => {
      const savedTheme = localStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-bs-theme', savedTheme);
    });

    // Toggle theme and save preference
    document.getElementById('darkModeToggle').addEventListener('click', () => {
      const html = document.documentElement;
      const currentTheme = html.getAttribute('data-bs-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-bs-theme', newTheme);
      localStorage.setItem('theme', newTheme);
    });


    const ctx = document.getElementById('userChart').getContext('2d');
    const userChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['<?php echo $translations[$lang]['admins']; ?>', '<?php echo $translations[$lang]['active_users']; ?>'],
        datasets: [{
          label: '<?php echo $translations[$lang]['user_count']; ?>',
          data: [<?= $total_admins ?>, <?= $active_users ?>],
          backgroundColor: ['#198754', '#ffc107'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        animation: {
          animateScale: true,
          animateRotate: true,
          duration: 1000,
          easing: 'easeOutQuart'
        },
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  </script>


</body>


</html>