<!-- Health Tips Page -->

<!-- PHP code -->
<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login if the user is not authenticated
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "User") {
    header("Location: /glucopredictor/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

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
        'recent_assessment' => 'Your Recent Assessment Result:',
        'date_label' => 'Date:',
        'risk_score_label' => 'Risk Score:',
        'risk_level_label' => 'Risk Level:',
        'Low Risk' => 'Low Risk',
        'Moderate Risk' => 'Moderate Risk',
        'High Risk' => 'High Risk',
        'health_tips_title' => 'Health Tips ❤️🩺💡',
        'ai_tips_title' => 'Bonus AI-Based Tip 🤖',
        'disclaimer' => '⚠️ Disclaimer: These tips are for informational purposes only and not a substitute for medical advice. ⚠️',
        'based_input' => '[Based on your input]',
        'no_assessment' => 'No recent assessment found.',
        'no_tips' => 'No health tips available. Complete an assessment.',
        'no_ai_tips' => 'No bonus AI-based tip available. Complete an assessment.',
        'logout_confirmation' => 'Are you sure you want to log out?',
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
        'recent_assessment' => 'Keputusan Penilaian Terkini Anda:',
        'date_label' => 'Tarikh:',
        'risk_score_label' => 'Skor Risiko:',
        'risk_level_label' => 'Tahap Risiko:',
        'Low Risk' => 'Risiko Rendah',
        'Moderate Risk' => 'Risiko Sederhana',
        'High Risk' => 'Risiko Tinggi',
        'health_tips_title' => 'Tip Kesihatan ❤️🩺💡',
        'ai_tips_title' => 'Tip Bonus Berasaskan AI 🤖',
        'disclaimer' => '⚠️ Penafian: Tip-tip ini adalah untuk tujuan maklumat sahaja dan bukan pengganti nasihat perubatan. ⚠️',
        'based_input' => '[Berdasarkan input anda]',
        'no_assessment' => 'Tiada penilaian terkini dijumpai.',
        'no_tips' => 'Tip kesihatan belum tersedia. Lengkapkan penilaian terlebih dahulu.',
        'no_ai_tips' => 'Tiada tip bonus berasaskan AI tersedia. Lengkapkan penilaian terlebih dahulu.',
        'logout_confirmation' => 'Adakah anda pasti ingin log keluar?',
    ],
];

include("../database.php");

// Fetch username from the database
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user["username"] ?? "Guest"; // Default username if not found

// Fetch the most recent risk assessment result
$sql = "SELECT r.result_date, r.risk_score, r.risk_level 
        FROM result r 
        JOIN assessment a ON r.assessment_id = a.assessment_id
        WHERE a.user_id = ?
        ORDER BY r.result_date DESC, r.result_id DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$date = $translations[$lang]['no_assessment'];
$score = $translations[$lang]['no_assessment'];
$level = $translations[$lang]['no_assessment'];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $date = $row["result_date"];
    $score = $row["risk_score"];
    $level = $row["risk_level"];
}

// Generate new health tips based on the assessment result
$query = "SELECT COUNT(*) FROM assessment WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($assessment_count);
$stmt->fetch();
$assessment_exists = $assessment_count > 0;
$stmt->close();

// Check if an assessment was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment'])) {
    // Generate and store new health tips
    $_SESSION['health_tips'][$user_id] = $new_tips; // Replace with new assessment tips
    $_SESSION['ai_tips'] = $ai_tips; // Replace with AI-generated tips
} elseif (!$assessment_exists) {
    // If no assessment exists in the database, clear session tips
    unset($_SESSION['health_tips'][$user_id]);
    unset($_SESSION['ai_tips']);
}

// Retrieve stored health tips if available
$health_tips = $_SESSION['health_tips'][$user_id] ?? [];

$ai_tips = isset($_SESSION['ai_tips']) ? $_SESSION['ai_tips'] : $translations[$lang]['no_ai_tips'];

$conn->close();
?>


<!-- HTML code -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Tips</title>
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

                <li><a href="tips.php" class="active">
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
                    <li class="username" style="color: blue;"><?php echo htmlspecialchars($username); ?></li>
                    <li>
                        <div class="text-center mt-2">
                            <a href="?lang=en"><?php echo $translations[$lang]['english']; ?></a> |
                            <a href="?lang=ms"><?php echo $translations[$lang]['malay']; ?></a>
                        </div>
                    </li>
                    <li><a href="edit.php"><?php echo $translations[$lang]['edit_profile']; ?></a></li>
                    <li><a href="logout.php" id="out"><?php echo $translations[$lang]['logout']; ?></a></li>
                </ul>
            </div>

        </div>
        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-heart-medical"></i>
                    <span class="text"><?php echo $translations[$lang]['health_tips']; ?></span>
                </div>


                <div class="result-container">
                    <div class="result-tips">
                        <h3><?php echo $translations[$lang]['recent_assessment']; ?></h3>
                        <p><?php echo $translations[$lang]['date_label']; ?> <?php echo $date; ?></p>
                        <p><?php echo $translations[$lang]['risk_score_label']; ?> <?php echo $score; ?></p>
                        <?php
                        $colors = [
                            'Low Risk' => 'chartreuse',
                            'Moderate Risk' => 'orange',
                            'High Risk' => 'red'
                        ];
                        ?>

                        <p>
                            <?= $translations[$lang]['risk_level_label']; ?>
                            <span style="color: <?= $colors[$level] ?? 'inherit'; ?>;">
                                <?= $translations[$lang][$level] ?? $level; ?>
                            </span>
                        </p>


                    </div>

                    <div class="health-tips-container">
                        <h3><?php echo $translations[$lang]['health_tips_title']; ?></h3>
                        <h5 style="color: red;" id="disclaimer"><?php echo $translations[$lang]['disclaimer']; ?></h5>

                        <!-- Displaying tips dynamically from PHP -->
                        <h5 class="based"><?php echo $translations[$lang]['based_input']; ?></h5>
                        <div id="instantTips">
                            <ul class="tips-list"></ul> <!-- Empty initially, will be populated by JS -->
                        </div>
                    </div>

                    <div class="bonus-tips">
                        <h4><?php echo $translations[$lang]['ai_tips_title']; ?></h4>
                        <p>
                            <?php
                            // Ensure the language key exists in the ai_tips array
                            if (isset($ai_tips[$lang]) && is_string($ai_tips[$lang])) {
                                // Display the tip based on the selected language
                                echo nl2br(htmlspecialchars($ai_tips[$lang]));
                            } else {
                                // If no tip exists for the selected language, show a default error message
                                echo nl2br(htmlspecialchars($translations[$lang]['no_ai_tips']));
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>

    </section>


    <!-- Javascript code -->
    <script>
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
        })
        // Get the profile picture and dropdown menu elements
        const profileToggle = document.getElementById('profileToggle');
        const dropdownMenu = document.getElementById('dropdownMenu');

        // Add an event listener to toggle the dropdown menu
        profileToggle.addEventListener('click', () => {
            // Toggle the visibility of the dropdown menu
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        // Close the dropdown menu if the user clicks outside it
        document.addEventListener('click', (event) => {
            if (!profileToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.style.display = 'none';
            }
        });


        let healthTips = <?php echo json_encode($health_tips); ?>;
        let currentLang = "<?php echo $lang; ?>"; // passed from PHP, e.g. 'en' or 'ms'

        document.addEventListener("DOMContentLoaded", function() {
            let container = document.getElementById("instantTips");
            let list = container.querySelector(".tips-list");

            if (healthTips.length > 0) {
                list.innerHTML = ""; // Clear previous content
                displayTipsWithAnimation(healthTips, list);
            } else {
                list.innerHTML = "<li><?php echo $translations[$lang]['no_tips']; ?></li>";
            }
        });

        // 🎬 Display tips with typewriter animation
        function displayTipsWithAnimation(tips, list) {
            function showNextTip(i) {
                if (i < tips.length) {
                    let listItem = document.createElement("li");
                    list.appendChild(listItem);

                    // Pick correct language version or fallback to English
                    let tipText = tips[i][currentLang] || tips[i]['en'];

                    typeWriterEffect(tipText, listItem, 25, function() {
                        showNextTip(i + 1);
                    });
                }
            }

            showNextTip(0);
        }

        // Typewriter effect function
        function typeWriterEffect(text, element, speed, callback) {
            let index = 0;

            function type() {
                if (index < text.length) {
                    element.innerHTML += text.charAt(index);
                    index++;
                    setTimeout(type, speed);
                } else if (callback) {
                    callback();
                }
            }
            type();
        }
    </script>

</body>

</html>