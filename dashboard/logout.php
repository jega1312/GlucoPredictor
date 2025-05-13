<!-- Logout from the session -->

<!-- PHP code -->
<?php

session_start();

// Step 1: Backup specific session data
$health_tips = $_SESSION['health_tips'] ?? [];
$ai_tips = $_SESSION['ai_tips'] ?? null;

// Step 2: Destroy the session
session_unset();
session_destroy();

// Step 3: Start a new session
session_start();

// Step 4: Restore the session data you want to retain
if (!empty($health_tips)) {
    $_SESSION['health_tips'] = $health_tips;
}
if (!empty($ai_tips)) {
    $_SESSION['ai_tips'] = $ai_tips;
}

// Redirect to the login page
header("Location: /glucopredictor/login.php");
exit();

?>