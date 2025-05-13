<!-- GlucoPredictor Dashboard Homepage -->

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
        'dashboard' => 'Dashboard',
        'evaluate_risk' => 'EVALUATE YOUR DIABETES RISK NOW',
        'about_diabetes' => 'About Diabetes',
        'diabetes_info' => "Diabetes is a long-term condition where the body either doesn't produce enough insulin or can't use it properly, causing blood sugar levels to become unbalanced. Proper management is crucial to avoid serious health complications.",
        'types' => 'Types of Diabetes',
        'type1' => 'Type 1',
        'type1_info' => "This happens when the body's immune system mistakenly destroys the cells that make insulin. Without insulin, the body can't control blood sugar properly. It usually starts in childhood or teenage years and requires daily insulin treatment.",
        'type2' => 'Type 2',
        'type2_info' => "The most common type, where the body doesn't use insulin properly or doesn't make enough of it. This can cause high blood sugar levels. It's often linked to lifestyle factors like diet and exercise but can also be influenced by genetics.",
        'gestational' => 'Gestational',
        'gestational_info' => "A temporary type of diabetes that some women develop during pregnancy. It usually goes away after childbirth but increases the risk of developing type 2 diabetes later in life.",
        'english' => 'EN',
        'malay' => 'BM',
        'edit_profile' => 'Edit Profile',
        'common_symptoms' => 'Common Symptoms',
        'weight_loss' => 'Weight Loss',
        'weight_loss_info' => 'High glucose prevents cells from using sugar, forcing the body to burn fat and muscle for energy.',
        'blurry_vision' => 'Blurred Vision',
        'blurry_vision_info' => 'Excess sugar affects eye lenses, causing swelling and temporary blurred vision.',
        'fatigue' => 'Fatigue',
        'fatigue_info' => 'Cells struggle to absorb glucose, leaving the body low on energy and causing constant tiredness.',
        'excessive_thirst' => 'Excessive Thirst',
        'excessive_thirst_info' => 'High blood sugar pulls fluid from tissues, making you feel extremely thirsty and hungry.',
        'frequent_urination' => 'Frequent Urination',
        'frequent_urination_info' => 'The kidneys work harder to remove excess sugar, leading to frequent urination, especially at night.',
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
        'dashboard' => 'Papan Pemuka',
        'evaluate_risk' => 'NILAI RISIKO DIABETES ANDA SEKARANG',
        'about_diabetes' => 'Tentang Diabetes',
        'diabetes_info' => "Diabetes adalah keadaan jangka panjang di mana badan sama ada tidak menghasilkan insulin yang mencukupi atau tidak dapat menggunakannya dengan betul, menyebabkan paras gula dalam darah menjadi tidak seimbang. Pengurusan yang betul adalah penting untuk mengelakkan komplikasi kesihatan yang serius.",
        'types' => 'Jenis Diabetes',
        'type1' => 'Jenis 1',
        'type1_info' => "Ini berlaku apabila sistem imun badan secara tidak sengaja memusnahkan sel yang menghasilkan insulin. Tanpa insulin, badan tidak dapat mengawal gula dalam darah dengan betul. Biasanya bermula pada zaman kanak-kanak atau remaja dan memerlukan rawatan insulin harian.",
        'type2' => 'Jenis 2',
        'type2_info' => "Jenis yang paling biasa, di mana badan tidak menggunakan insulin dengan betul atau tidak menghasilkan insulin yang mencukupi. Ini boleh menyebabkan paras gula dalam darah tinggi. Ia sering dikaitkan dengan faktor gaya hidup seperti diet dan senaman tetapi juga boleh dipengaruhi oleh genetik.",
        'gestational' => 'Gestasi',
        'gestational_info' => "Jenis diabetes sementara yang dialami oleh sesetengah wanita semasa kehamilan. Ia biasanya hilang selepas bersalin tetapi meningkatkan risiko menghidap diabetes jenis 2 kemudian.",
        'english' => 'EN',
        'malay' => 'BM',
        'edit_profile' => 'Kemaskini Profil',
        'common_symptoms' => 'Simptom Umum',
        'weight_loss' => 'Penurunan Berat Badan',
        'weight_loss_info' => 'Glukosa tinggi menghalang sel daripada menggunakan gula, memaksa tubuh membakar lemak dan otot untuk tenaga.',
        'blurry_vision' => 'Penglihatan Kabur',
        'blurry_vision_info' => 'Gula berlebihan menjejaskan kanta mata, menyebabkan pembengkakan dan penglihatan kabur sementara.',
        'fatigue' => 'Keletihan',
        'fatigue_info' => 'Sel sukar menyerap glukosa, menyebabkan tubuh kekurangan tenaga dan keletihan berterusan.',
        'excessive_thirst' => 'Dahaga Berlebihan',
        'excessive_thirst_info' => 'Gula tinggi menarik cecair dari tisu, menyebabkan rasa dahaga dan lapar yang melampau.',
        'frequent_urination' => 'Kekerapan Kencing',
        'frequent_urination_info' => 'Buah pinggang bekerja lebih keras untuk menyingkirkan gula berlebihan, menyebabkan kencing kerap, terutamanya pada waktu malam.',
        'logout_confirmation' => 'Adakah anda pasti ingin log keluar?',
    ],
];

include("../database.php");

$user_id = $_SESSION["user_id"];

// Fetch username from the database
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user["username"] ?? ""; // Default username if not found

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
    <title><?php echo $translations[$lang]['home']; ?></title>
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
                <li><a href="index.php" class="active">
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
                    <i class="uil uil-tachometer-fast-alt"></i>
                    <span class="text"><?php echo $translations[$lang]['dashboard']; ?></span>
                </div>

                <a href="form.php">
                    <div class="box">
                        <div class="box1">
                            <i class="uil uil-file-medical-alt"></i>
                            <span class="text"><?php echo $translations[$lang]['evaluate_risk']; ?></span>
                        </div>
                    </div>
                </a>

                <div class="about-container">
                    <div class="box">
                        <div class="box2">
                            <h2><?php echo $translations[$lang]['about_diabetes']; ?></h2>
                            <img src="images/diabetes.png" alt="diabetesicon" class="diabetes-icon">
                            <p><?php echo $translations[$lang]['diabetes_info']; ?></p>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box3">
                            <div class="carousel-container">
                                <h2><?php echo $translations[$lang]['types']; ?></h2>
                                <div class="carousel">
                                    <div class="carousel-track">
                                        <div class="card">
                                            <h3><?php echo $translations[$lang]['type1']; ?></h3>
                                            <p><?php echo $translations[$lang]['type1_info']; ?></p>
                                        </div>
                                        <div class="card">
                                            <h3><?php echo $translations[$lang]['type2']; ?></h3>
                                            <p><?php echo $translations[$lang]['type2_info']; ?></p>
                                        </div>
                                        <div class="card">
                                            <h3><?php echo $translations[$lang]['gestational']; ?></h3>
                                            <p><?php echo $translations[$lang]['gestational_info']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="carousel-buttons">
                                    <button id="prev" disabled>&larr;</button>
                                    <button id="next">&rarr;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-container">
                    <div class="box">
                        <div class="box4">
                            <h2 class="section-heading"><?php echo $translations[$lang]['common_symptoms']; ?></h2>
                            <div class="symptom-grid">
                                <div class="symptom-card">
                                    <h3><?php echo $translations[$lang]['weight_loss']; ?></h3>
                                    <p><?php echo $translations[$lang]['weight_loss_info']; ?></p>
                                </div>
                                <div class="symptom-card">
                                    <h3><?php echo $translations[$lang]['blurry_vision']; ?></h3>
                                    <p><?php echo $translations[$lang]['blurry_vision_info']; ?></p>
                                </div>
                                <div class="symptom-card">
                                    <h3><?php echo $translations[$lang]['excessive_thirst']; ?></h3>
                                    <p><?php echo $translations[$lang]['excessive_thirst_info']; ?></p>
                                </div>
                                <div class="symptom-card">
                                    <h3><?php echo $translations[$lang]['frequent_urination']; ?></h3>
                                    <p><?php echo $translations[$lang]['frequent_urination_info']; ?></p>
                                </div>
                                <div class="symptom-card">
                                    <h3><?php echo $translations[$lang]['fatigue']; ?></h3>
                                    <p><?php echo $translations[$lang]['fatigue_info']; ?></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
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


        const track = document.querySelector('.carousel-track');
        const cards = document.querySelectorAll('.card');
        const prevButton = document.getElementById('prev');
        const nextButton = document.getElementById('next');

        let currentIndex = 0;

        const updateButtons = () => {
            prevButton.disabled = currentIndex === 0;
            nextButton.disabled = currentIndex === cards.length - 1;
        };

        prevButton.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                track.style.transform = `translateX(-${currentIndex * 100}%)`;
                updateButtons();
            }
        });

        nextButton.addEventListener('click', () => {
            if (currentIndex < cards.length - 1) {
                currentIndex++;
                track.style.transform = `translateX(-${currentIndex * 100}%)`;
                updateButtons();
            }
        });
    </script>


</body>


</html>