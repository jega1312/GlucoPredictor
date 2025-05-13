<!-- Weight Tracking Page  -->

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
    'weight' => 'Weight (kg)',
    'height' => 'Height (cm)',
    'bmi' => 'BMI',
    'action' => 'Action',
    'add_weight_entry' => 'Add Weight Entry',
    'date_label' => 'Date:',
    'weight_label' => 'Weight:',
    'height_label' => 'Height:',
    'bmi_label' => 'BMI:',
    'save' => 'Save',
    'weight_level' => 'Weight Level (kg)',
    'clear' => 'Clear',
    'empty_fields' => 'All fields are required!',
    'confirm_delete' => 'Are you sure you want to delete this entry?',
    'placeholder' => 'Automatically Calculated',
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
    'weight' => 'Berat (kg)',
    'height' => 'Tinggi (cm)',
    'bmi' => 'BMI',
    'action' => 'Tindakan',
    'add_weight_entry' => 'Tambah Entri Berat',
    'date_label' => 'Tarikh:',
    'weight_label' => 'Berat:',
    'height_label' => 'Tinggi:',
    'bmi_label' => 'BMI:',
    'save' => 'Simpan',
    'weight_level' => 'Tahap Berat (kg)',
    'clear' => 'Padam',
    'empty_fields' => 'Semua medan diperlukan!',
    'confirm_delete' => 'Adakah anda pasti untuk memadam entri ini?',
    'placeholder' => 'Dihitung secara automatik',
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
  <title><?php echo $translations[$lang]['weight_tracking']; ?></title>
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

        <li><a href="glucose.php">
            <i class="uil uil-tear"></i>
            <p class="link-name"><?php echo $translations[$lang]['glucose_tracking']; ?></p>
          </a></li>

        <li><a href="weight.php" class="active">
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
          <i class="uil uil-weight"></i>
          <span class="text"><?php echo $translations[$lang]['weight_tracking']; ?></span>
        </div>

        <div class="page-header">
          <h1><?php echo $translations[$lang]['weight_tracking']; ?></h1>
          <button class="btn" id="addEntryButton"><?php echo $translations[$lang]['add_entry']; ?></button>
        </div>

        <!-- Weight Chart -->
        <div class="chart-section">
          <canvas id="weightChart"></canvas>
        </div>

        <!-- History Section -->
        <div class="weight-history-section">
          <div class="title">
            <i class="uil uil-book-medical"></i>
            <span class="text"><?php echo $translations[$lang]['past_entries']; ?></span>
          </div>
          <table>
            <thead>
              <tr>
                <th><?php echo $translations[$lang]['date']; ?></th>
                <th><?php echo $translations[$lang]['weight']; ?></th>
                <th><?php echo $translations[$lang]['height']; ?></th>
                <th><?php echo $translations[$lang]['bmi']; ?></th>
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
            <h2><?php echo $translations[$lang]['add_weight_entry']; ?></h2>
            <form id="weightForm">
              <label for="date"><?php echo $translations[$lang]['date_label']; ?></label>
              <input type="date" id="weightDate" required><br>

              <label for="weight"><?php echo $translations[$lang]['weight_label']; ?></label>
              <input type="number" id="weight" placeholder="kg" oninput="calculateBMI()" min="0" required><br>

              <label for="height"><?php echo $translations[$lang]['height_label']; ?></label>
              <input type="number" id="height" placeholder="cm" oninput="calculateBMI()" min="0" required><br>

              <label for="bmi"><?php echo $translations[$lang]['bmi_label']; ?></label>
              <input type="number" id="bmi" placeholder="<?php echo $translations[$lang]['placeholder']; ?>" style="cursor: default" readonly><br>

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

    // Profile Dropdown
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

    // Get Logged-in User ID from PHP
    const user_id = "<?php echo $_SESSION['user_id']; ?>";

    // Function to get user-specific storage key
    function getUserStorageKey() {
      return `weightEntries_${user_id}`;
    }

    // Weight Tracking Elements
    const addWeightEntryButton = document.getElementById('addEntryButton');
    const addWeightEntryModal = document.getElementById('addEntryModal');
    const closeWeightModal = document.getElementById('closeModal');
    const weightForm = document.getElementById('weightForm');
    const entriesWeightTableBody = document.getElementById('entriesTableBody');

    // Open Modal
    addWeightEntryButton.addEventListener('click', () => {
      addWeightEntryModal.style.display = 'flex';
    });

    // Close Modal
    closeWeightModal.addEventListener('click', () => {
      addWeightEntryModal.style.display = 'none';
    });

    // Load User-Specific Data from localStorage
    function loadWeightEntries() {
      const savedEntries = JSON.parse(localStorage.getItem(getUserStorageKey())) || [];
      entriesWeightTableBody.innerHTML = ""; // Clear existing table

      savedEntries.forEach((entry, index) => {
        addWeightTableRow(entry, index);
      });

      updateChart(savedEntries);
    }

    // Save User-Specific Data
    function saveWeightEntry(entry) {
      const savedEntries = JSON.parse(localStorage.getItem(getUserStorageKey())) || [];
      savedEntries.push(entry);
      localStorage.setItem(getUserStorageKey(), JSON.stringify(savedEntries));
    }

    // Add Row to Table
    function addWeightTableRow(entry, index) {
      const newRow = document.createElement('tr');
      newRow.setAttribute('data-index', index);
      newRow.innerHTML = `
            <td>${entry.date}</td>
            <td>${entry.weight}</td>
            <td>${entry.height}</td>
            <td>${entry.bmi}</td>
            <td><button class="delete-btn"><?php echo $translations[$lang]['clear']; ?></button></td>
        `;

      const deleteButton = newRow.querySelector('.delete-btn');
      deleteButton.addEventListener('click', () => deleteWeightRow(index, newRow));

      entriesWeightTableBody.appendChild(newRow);
    }

    // Handle Form Submission
    weightForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const weightDate = document.getElementById('weightDate').value;
      const weight = document.getElementById('weight').value;
      const height = document.getElementById('height').value;
      const bmi = document.getElementById('bmi').value;

      if (!weightDate || isNaN(weight) || isNaN(height) || isNaN(bmi)) {
        alert('<?php echo $translations[$lang]['empty_fields']; ?>');
        return;
      }

      const entry = {
        date: weightDate,
        weight,
        height,
        bmi
      };

      // Save to localStorage
      saveWeightEntry(entry);

      // Add to table
      addWeightTableRow(entry, JSON.parse(localStorage.getItem(getUserStorageKey())).length - 1);

      weightForm.reset();
      addWeightEntryModal.style.display = 'none';

      updateChart(JSON.parse(localStorage.getItem(getUserStorageKey())));
    });

    // Calculate BMI
    function calculateBMI() {
      const weight = parseFloat(document.getElementById('weight').value);
      const height = parseFloat(document.getElementById('height').value) / 100; // Convert cm to meters

      if (weight > 0 && height > 0) {
        const bmi = (weight / (height * height)).toFixed(2);
        document.getElementById('bmi').value = bmi;
      } else {
        document.getElementById('bmi').value = '';
      }
    }

    // Delete Individual Row
    function deleteWeightRow(index, rowElement) {
      if (confirm('<?php echo $translations[$lang]['confirm_delete']; ?>')) {
        let savedEntries = JSON.parse(localStorage.getItem(getUserStorageKey())) || [];
        savedEntries.splice(index, 1);
        localStorage.setItem(getUserStorageKey(), JSON.stringify(savedEntries));

        rowElement.remove();
        reindexWeightTableRows();
        updateChart(savedEntries);
      }
    }

    // Re-index Table Rows
    function reindexWeightTableRows() {
      const rows = entriesWeightTableBody.querySelectorAll('tr');
      rows.forEach((row, newIndex) => {
        row.setAttribute('data-index', newIndex);
        const deleteButton = row.querySelector('.delete-btn');
        deleteButton.onclick = () => deleteWeightRow(newIndex, row);
      });
    }

    // Initialize Chart.js for Weight Tracking
    const ctx = document.getElementById('weightChart').getContext('2d');
    let weightChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [], // Dates (X-axis)
        datasets: [{
          label: '<?php echo $translations[$lang]['weight']; ?>',
          data: [], // Weight values
          borderColor: '#004aad',
          backgroundColor: 'rgba(0, 0, 255, 0.2)',
          fill: true,
          borderWidth: 2,
          tension: 0.4,
        }],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: (context) => `${context.dataset.label}: ${context.raw} kg`,
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
              text: '<?php echo $translations[$lang]['weight']; ?>'
            },
            beginAtZero: false
          },
        },
      },
    });

    // Load User-Specific Data into Chart
    function updateChart(savedEntries) {
      weightChart.data.labels = savedEntries.map(entry => entry.date);
      weightChart.data.datasets[0].data = savedEntries.map(entry => entry.weight);
      weightChart.update();
    }

    // Initial Load
    document.addEventListener('DOMContentLoaded', loadWeightEntries);
  </script>


</body>


</html>