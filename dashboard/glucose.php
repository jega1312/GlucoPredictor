<!-- Glucose Tracking Page -->

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
    'english' => 'EN',
    'malay' => 'BM',
    'edit_profile' => 'Edit Profile',
    'add_entry' => '+ Add Entry',
    'past_entries' => 'Past Entries',
    'date' => 'Date',
    'fasting' => 'Fasting (mg/dL)',
    'random' => 'Random (mg/dL)',
    'action' => 'Action',
    'add_glucose_entry' => 'Add Glucose Entry',
    'date_label' => 'Date:',
    'fasting_label' => 'Fasting:',
    'random_label' => 'Random:',
    'save' => 'Save',
    'glucose_level' => 'Glucose Level (mg/dL)',
    'clear' => 'Clear',
    'empty_fields' => 'All fields are required!',
    'confirm_delete' => 'Are you sure you want to delete this entry?',
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
    'add_entry' => '+ Tambah Entri',
    'past_entries' => 'Entri Terdahulu',
    'date' => 'Tarikh',
    'fasting' => 'Puasa (mg/dL)',
    'random' => 'Rawak (mg/dL)',
    'action' => 'Tindakan',
    'add_glucose_entry' => 'Tambah Entri Glukosa',
    'date_label' => 'Tarikh:',
    'fasting_label' => 'Puasa:',
    'random_label' => 'Rawak:',
    'save' => 'Simpan',
    'glucose_level' => 'Tahap Glukosa (mg/dL)',
    'clear' => 'Padam',
    'empty_fields' => 'Semua medan diperlukan!',
    'confirm_delete' => 'Adakah anda pasti untuk memadam entri ini?',
    'logout_confirmation' => 'Adakah anda pasti untuk log keluar?',
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
$username = $user["username"] ?? "Guest"; // Default username if not found

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
  <title><?php echo $translations[$lang]['glucose_tracking']; ?></title>
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

        <li><a href="history.php">
            <i class="uil uil-history"></i>
            <p class="link-name"><?php echo $translations[$lang]['history']; ?></p>
          </a></li>

        <li><a href="glucose.php" class="active">
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
          <i class="uil uil-tear"></i>
          <span class="text"><?php echo $translations[$lang]['glucose_tracking']; ?></span>
        </div>

        <div class="page-header">
          <h1><?php echo $translations[$lang]['glucose_tracking']; ?></h1>
          <button class="btn" id="addEntryButton"><?php echo $translations[$lang]['add_entry']; ?></button>
        </div>

        <!-- Glucose Chart -->
        <div class="chart-section">
          <canvas id="glucoseChart"></canvas>
        </div>

        <!-- History Section -->
        <div class="glucose-history-section">
          <div class="title">
            <i class="uil uil-book-medical"></i>
            <span class="text"><?php echo $translations[$lang]['past_entries']; ?></span>
          </div>
          <table>
            <thead>
              <tr>
                <th><?php echo $translations[$lang]['date']; ?></th>
                <th><?php echo $translations[$lang]['fasting']; ?></th>
                <th><?php echo $translations[$lang]['random']; ?></th>
                <th><?php echo $translations[$lang]['action']; ?></th>
              </tr>
            </thead>
            <tbody id="entriesTableBody">
              <!-- Dynamically populated rows -->
            </tbody>
          </table>
        </div>

        <!-- Modal for Adding Entry -->
        <div class="modal" id="addEntryModal">
          <div class="modal-content">
            <span class="close-button" id="closeModal">&times;</span>
            <h2><?php echo $translations[$lang]['add_glucose_entry']; ?></h2>
            <form id="glucoseForm">
              <label for="date"><?php echo $translations[$lang]['date_label']; ?></label>
              <input type="date" id="glucoseDate" required><br>

              <label for="fasting"><?php echo $translations[$lang]['fasting_label']; ?></label>
              <input type="number" id="fasting" placeholder="mg/dL" min="0" required><br>

              <label for="random"><?php echo $translations[$lang]['random_label']; ?></label>
              <input type="number" id="random" placeholder="mg/dL" min="0" required><br>

              <button type="submit" class="btn"><?php echo $translations[$lang]['save']; ?></button>
            </form>
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

    sidebar = body.querySelector("nav"),
      sidebarToggle = body.querySelector(".sidebar-toggle");

    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("close");
    });

    const profileToggle = document.getElementById('profileToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');

    profileToggle.addEventListener('click', () => {
      dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (event) => {
      if (!profileToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.style.display = 'none';
      }
    });

    // 🔹 Glucose Tracking - User-Specific Data Handling
    const userId = <?php echo json_encode($_SESSION["user_id"]); ?>; // Get user ID from PHP

    // Function to get user-specific storage key
    function getStorageKey() {
      return `glucoseEntries_${userId}`;
    }

    const addGlucoseEntryButton = document.getElementById('addEntryButton');
    const addGlucoseEntryModal = document.getElementById('addEntryModal');
    const closeGlucoseModal = document.getElementById('closeModal');
    const glucoseForm = document.getElementById('glucoseForm');
    const entriesGlucoseTableBody = document.getElementById('entriesTableBody');

    addGlucoseEntryButton.addEventListener('click', () => {
      addGlucoseEntryModal.style.display = 'flex';
    });

    closeGlucoseModal.addEventListener('click', () => {
      addGlucoseEntryModal.style.display = 'none';
    });

    // Load Data from localStorage (User-Specific)
    function loadEntries() {
      const savedEntries = JSON.parse(localStorage.getItem(getStorageKey())) || [];
      savedEntries.forEach((entry, index) => {
        addTableRow(entry, index);
      });
    }

    // Save Data to localStorage (User-Specific)
    function saveEntry(entry) {
      const savedEntries = JSON.parse(localStorage.getItem(getStorageKey())) || [];
      savedEntries.push(entry);
      localStorage.setItem(getStorageKey(), JSON.stringify(savedEntries));
    }

    // Add Row to Table
    function addTableRow(entry, index) {
      const newRow = document.createElement('tr');
      newRow.setAttribute('data-index', index);
      newRow.innerHTML = `
            <td>${entry.date}</td>
            <td>${entry.fasting}</td>
            <td>${entry.random}</td>
            <td><button class="delete-btn"><?php echo $translations[$lang]['clear']; ?></button></td>
        `;

      // Add delete functionality
      const deleteButton = newRow.querySelector('.delete-btn');
      deleteButton.addEventListener('click', () => deleteRow(index, newRow));

      entriesGlucoseTableBody.appendChild(newRow);
    }

    // Handle Form Submission
    glucoseForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const glucoseDate = document.getElementById('glucoseDate').value;
      const fasting = parseFloat(document.getElementById('fasting').value);
      const random = parseFloat(document.getElementById('random').value);

      if (!glucoseDate || isNaN(fasting) || isNaN(random)) {
        alert('<?php echo $translations[$lang]['empty_fields']; ?>');
        return;
      }

      const entry = {
        date: glucoseDate,
        fasting,
        random
      };

      // Save to localStorage (User-Specific)
      saveEntry(entry);

      // Add to table
      addTableRow(entry, JSON.parse(localStorage.getItem(getStorageKey())).length - 1);

      // Update Chart Instantly  
      addEntryToChart(glucoseDate, fasting, random);

      glucoseForm.reset();
      addGlucoseEntryModal.style.display = 'none';
    });

    // Delete Individual Row
    function deleteRow(index, rowElement) {
      if (confirm('<?php echo $translations[$lang]['confirm_delete']; ?>')) {
        const savedEntries = JSON.parse(localStorage.getItem(getStorageKey())) || [];
        savedEntries.splice(index, 1);
        localStorage.setItem(getStorageKey(), JSON.stringify(savedEntries));

        rowElement.remove();
        reindexTableRows();
        updateChart();
      }
    }

    // Re-index Table Rows
    function reindexTableRows() {
      const rows = entriesGlucoseTableBody.querySelectorAll('tr');
      rows.forEach((row, newIndex) => {
        row.setAttribute('data-index', newIndex);
        const deleteButton = row.querySelector('.delete-btn');
        deleteButton.onclick = () => deleteRow(newIndex, row);
      });
    }

    // Chart.js for Glucose Tracking
    const ctx = document.getElementById('glucoseChart').getContext('2d');
    let glucoseChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [{
            label: '<?php echo $translations[$lang]['fasting']; ?>',
            data: [],
            borderColor: '#004aad',
            backgroundColor: 'rgba(0, 0, 255, 0.2)',
            borderWidth: 2,
            tension: 0.4,
          },
          {
            label: '<?php echo $translations[$lang]['random']; ?>',
            data: [],
            borderColor: 'red',
            backgroundColor: 'rgba(255, 0, 0, 0.2)',
            borderWidth: 2,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: (context) => `${context.dataset.label}: ${context.raw} mg/dL`,
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
              text: '<?php echo $translations[$lang]['glucose_level']; ?>'
            },
            beginAtZero: false
          },
        },
      },
    });

    // Load Data into Chart (User-Specific)
    function loadChartEntries() {
      const savedEntries = JSON.parse(localStorage.getItem(getStorageKey())) || [];
      glucoseChart.data.labels = [];
      glucoseChart.data.datasets[0].data = [];
      glucoseChart.data.datasets[1].data = [];

      savedEntries.forEach((entry) => {
        glucoseChart.data.labels.push(entry.date);
        glucoseChart.data.datasets[0].data.push(entry.fasting);
        glucoseChart.data.datasets[1].data.push(entry.random);
      });
      glucoseChart.update();
    }

    // Add New Entry to Chart
    function addEntryToChart(date, fasting, random) {
      glucoseChart.data.labels.push(date);
      glucoseChart.data.datasets[0].data.push(fasting);
      glucoseChart.data.datasets[1].data.push(random);
      glucoseChart.update();
    }

    // Update Chart after Deletion
    function updateChart() {
      loadChartEntries();
    }

    // Load chart data on page load
    document.addEventListener('DOMContentLoaded', () => {
      loadEntries();
      loadChartEntries();
    });
  </script>


</body>


</html>