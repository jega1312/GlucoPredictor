<!-- History Page -->

<!-- PHP code -->
<?php
session_start([
    'use_only_cookies' => true,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict'
]);

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");


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
        'history' => 'History',
        'evaluate_risk' => 'EVALUATE YOUR DIABETES RISK NOW',
        'risk_score_history' => 'Risk Score History',
        'date' => 'Date',
        'risk_score' => 'Risk Score',
        'risk_level' => 'Risk Level',
        'no_history' => 'No risk history found.',
        'Low Risk' => 'Low Risk',
        'Moderate Risk' => 'Moderate Risk',
        'High Risk' => 'High Risk',
        'english' => 'EN',
        'malay' => 'BM',
        'edit_profile' => 'Edit Profile',
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
        'history' => 'Sejarah',
        'evaluate_risk' => 'NILAI RISIKO DIABETES ANDA SEKARANG',
        'risk_score_history' => 'Sejarah Skor Risiko',
        'date' => 'Tarikh',
        'risk_score' => 'Skor Risiko',
        'risk_level' => 'Tahap Risiko',
        'no_history' => 'Tiada sejarah risiko dijumpai.',
        'Low Risk' => 'Risiko Rendah',
        'Moderate Risk' => 'Risiko Sederhana',
        'High Risk' => 'Risiko Tinggi',
        'english' => 'EN',
        'malay' => 'BM',
        'edit_profile' => 'Kemaskini Profil',
        'logout_confirmation' => 'Adakah anda pasti ingin log keluar?',
    ],
];

include("../database.php");

$user_id = $_SESSION["user_id"];

// Validate user_id
if (!is_numeric($user_id)) {
    die("Invalid user ID.");
}

// Fetch username from the database
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = htmlspecialchars($user["username"] ?? "Guest"); // Default username if not found

// Fetch the user's assessment results only
$query = "SELECT r.risk_score, r.risk_level, r.result_date 
          FROM result r
          JOIN assessment a ON r.assessment_id = a.assessment_id
          WHERE a.user_id = ? 
          ORDER BY r.result_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Prevent XSS in risk score history
        $row['result_date'] = htmlspecialchars($row['result_date']);
        $row['risk_score'] = htmlspecialchars($row['risk_score']);
        $row['risk_level'] = htmlspecialchars($row['risk_level']);
        $history[] = $row;
    }
}

$stmt->close();
$conn->close();
?>


<!-- HTML code -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations[$lang]['history']; ?></title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" href="images/logo-favicon.png">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

                <li><a href="history.php" class="active">
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
                    <li><a href="#" id="logoutBtn">
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
                    <i class="uil uil-history"></i>
                    <span class="text"><?php echo $translations[$lang]['history']; ?></span>
                </div>

                <a href="form.php">
                    <div class="box">
                        <div class="box1">
                            <i class="uil uil-file-medical-alt"></i>
                            <span class="text"><?php echo $translations[$lang]['evaluate_risk']; ?></span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="history">
                <div class="title">
                    <i class="uil uil-history-alt"></i>
                    <span class="text"><?php echo $translations[$lang]['risk_score_history']; ?></span>
                </div>

                <div class="chart-container">
                    <canvas id="historyChart"></canvas>
                </div>


                <table id="historyTable">
                    <thead>
                        <tr>
                            <th><?php echo $translations[$lang]['date']; ?></th>
                            <th><?php echo $translations[$lang]['risk_score']; ?></th>
                            <th><?php echo $translations[$lang]['risk_level']; ?></th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $row): ?>
                                <tr>
                                    <td><?php echo $row['result_date']; ?></td>
                                    <td><?php echo $row['risk_score']; ?></td>
                                    <?php
                                    // Normalize risk level to lowercase to match array keys
                                    $risk = ucwords(strtolower($row['risk_level']));
                                    $colors = [
                                        'Low Risk' => 'green',
                                        'Moderate Risk' => 'orange',
                                        'High Risk' => 'red'
                                    ];

                                    // Default color in case risk level is unknown
                                    $color = $colors[$risk] ?? 'black';
                                    ?>

                                    <td style="color: <?= $color; ?>;">
                                        <?= $translations[$lang][$risk]; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3"><?php echo $translations[$lang]['no_history']; ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <!-- JavaScript code -->
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

        });
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


        // Convert PHP history data to JavaScript arrays
        var historyData = <?php echo json_encode($history); ?>;

        // Extract dates and risk scores
        var dates = historyData.map(item => item.result_date);
        var riskScores = historyData.map(item => item.risk_score);

        // Call function to render the chart
        renderHistoryChart(dates, riskScores);

        function renderHistoryChart(dates, riskScores) {
            var ctx = document.getElementById('historyChart').getContext('2d');
            let historyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: '<?php echo $translations[$lang]['risk_score']; ?>',
                        data: riskScores,
                        borderColor: '#004aad',
                        backgroundColor: 'rgba(0, 0, 255, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4, // Smooth curve
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.dataset.label}: ${context.raw}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: '<?php echo $translations[$lang]['date']; ?>'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: '<?php echo $translations[$lang]['risk_score']; ?>'
                            },
                            beginAtZero: true
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuad' // Smooth animation
                    }
                }
            });
        }
    </script>


</body>


</html>