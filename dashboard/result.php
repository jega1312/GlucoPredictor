<!-- GlucoPredictor Risk Assessment Result Page -->

<!-- PHP code -->
<?php
// Set session cookie parameters BEFORE starting the session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // Only use secure cookies over HTTPS
    'httponly' => true, // Prevents JavaScript access
    'samesite' => 'Strict' // Protects against CSRF
]);

session_start(); // Start session only once

// Prevent session fixation
if (!isset($_SESSION["initiated"])) {
    session_regenerate_id(true);
    $_SESSION["initiated"] = true;
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "User") {
    header("Location: /glucopredictor/login.php");
    exit();
}

// Handle Language Selection
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'en'; // Default language
}

// Language Translations
$translations = [
    'en' => [
        'title' => 'GlucoPredictor Risk Assessment Result',
        'heading' => 'Diabetes Risk Assessment Result',
        'score_text' => 'Your calculated risk score is:',
        'risk_level' => 'Risk Level:',
        'get_tips' => 'Get Health Tips',
        'disclaimer' => 'Disclaimer: This result is for informational purposes only and not medical advice.'
    ],
    'ms' => [
        'title' => 'GlucoPredictor Keputusan Penilaian Risiko',
        'heading' => 'Keputusan Penilaian Risiko Diabetes',
        'score_text' => 'Skor risiko anda yang dikira ialah:',
        'risk_level' => 'Tahap Risiko:',
        'get_tips' => 'Dapatkan Tip Kesihatan',
        'disclaimer' => 'Penafian: Keputusan ini hanya untuk tujuan maklumat dan bukan nasihat perubatan.'
    ]
];

// Set language texts
$title = $translations[$lang]['title'];
$heading = $translations[$lang]['heading'];
$score_text = $translations[$lang]['score_text'];
$risk_level = $translations[$lang]['risk_level'];
$get_tips = $translations[$lang]['get_tips'];
$disclaimer = $translations[$lang]['disclaimer'];

include("../database.php");

$user_id = $_SESSION["user_id"];

// CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Retrieve the latest assessment_id for the user
$query = "SELECT assessment_id FROM assessment WHERE user_id = ? ORDER BY assessment_id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($assessment_id);
$stmt->fetch();
$stmt->close();

if (!$assessment_id) {
    echo "Error: No assessment found.";
    exit();
}

// Handle POST request securely
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    if (isset($_POST['riskScore']) && isset($_POST['riskLevel'])) {
        $riskScore = intval($_POST['riskScore']); // Ensure it's an integer
        $riskLevel = htmlspecialchars($_POST['riskLevel'], ENT_QUOTES, 'UTF-8'); // Prevent XSS
        $resultDate = date("Y-m-d");

        // Check if a result already exists for this assessment_id
        $check_stmt = $conn->prepare("SELECT result_id FROM result WHERE assessment_id = ?");
        $check_stmt->bind_param("i", $assessment_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "Result already exists. Preventing duplication.";
        } else {
            // Prepare and execute the SQL statement
            $stmt = $conn->prepare("INSERT INTO result (assessment_id, risk_score, risk_level, result_date) VALUES (?, ?, ?, ?)");

            if ($stmt) {
                $stmt->bind_param("iiss", $assessment_id, $riskScore, $riskLevel, $resultDate);
                if ($stmt->execute()) {
                    echo "Success";
                } else {
                    error_log("Database error: " . $stmt->error);
                    echo "Error saving data.";
                }
                $stmt->close();
            } else {
                error_log("SQL Preparation Error: " . $conn->error);
                echo "Error preparing statement.";
            }
        }
        $check_stmt->close();
        $conn->close();
    }
}

?>


<!-- HTML code -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations[$lang]['title']; ?></title>
    <link rel="shortcut icon" href="images/logo-favicon.png">

    <!-- CSS code -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #004AAD;
        }

        .result-container {
            background-color: #fff;
            text-align: center;
            max-width: 400px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 2rem;
            cursor: default;
        }

        h2 {
            font-size: 2em;
            color: black;
        }

        span {
            color: #004AAD;
        }

        .risk-score {
            font-size: 2em;
            font-weight: bold;
            color: #004AAD;
        }

        .redirect-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 15px 100px;
            font-size: 1em;
            color: #fff;
            background-color: #004AAD;
            border: none;
            border-radius: 5rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .redirect-btn:hover {
            background-color: #007bff;
        }
    </style>
</head>

<body>

    <div class="result-container">
        <h2><span>Gluco</span>Predictor</h2>
        <h3><?php echo $heading; ?></h3>


        <!-- Language Selection -->
        <div class="lang-box" style="text-align: center;">
            <a href="?lang=en">EN</a> | <a href="?lang=ms">BM</a>
        </div>

        <p><?php echo $score_text; ?></p>
        <p class="risk-score" id="riskScore"></p>
        <p id="riskLevel"></p>

        <h5 style="color: red; text-align: center;"><?php echo $disclaimer; ?></h5>

        <a href="tips.php" class="redirect-btn"><?php echo $get_tips; ?></a>
    </div>

    <!-- JavaScript code -->
    <script>
        // Fetch the risk score and category from sessionStorage
        const riskScore = sessionStorage.getItem('riskScore') || 'N/A';
        const riskLevel = sessionStorage.getItem('riskLevel') || 'Undefined';

        // Define PHP-translated risk levels
        const riskTranslations = {
            'en': {
                'Low Risk': 'Low Risk',
                'Moderate Risk': 'Moderate Risk',
                'High Risk': 'High Risk',
                'Undefined': 'Undefined'
            },
            'ms': {
                'Low Risk': 'Risiko Rendah',
                'Moderate Risk': 'Risiko Sederhana',
                'High Risk': 'Risiko Tinggi',
                'Undefined': 'Tidak Ditetapkan'
            }
        };

        // Get the current language from PHP
        const currentLang = "<?php echo $lang; ?>";

        // Translate risk level based on selected language
        const translatedRiskLevel = riskTranslations[currentLang][riskLevel] || riskTranslations[currentLang]['Undefined'];

        // Display the values on the page
        document.getElementById('riskScore').innerText = riskScore;

        const riskLevelElement = document.getElementById('riskLevel');
        riskLevelElement.innerText = `<?php echo $translations[$lang]['risk_level']; ?> ${translatedRiskLevel}`;

        // Set risk category color based on value
        switch (riskLevel) {
            case 'Low Risk':
                riskLevelElement.style.color = 'green';
                break;
            case 'Moderate Risk':
                riskLevelElement.style.color = 'darkorange';
                break;
            case 'High Risk':
                riskLevelElement.style.color = 'red';
                break;
            default:
                riskLevelElement.style.color = 'black';
        }

        // Send data to store result.php via AJAX securely
        fetch("result.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: `riskScore=${encodeURIComponent(riskScore)}&riskLevel=${encodeURIComponent(riskLevel)}&csrf_token=${encodeURIComponent("<?php echo $_SESSION['csrf_token']; ?>")}`
            })
            .then(response => response.text())
            .then(data => console.log("Response:", data))
            .catch(error => console.error("Error:", error));
    </script>


</body>


</html>