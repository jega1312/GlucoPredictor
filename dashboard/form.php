<!-- GlucoPredictor Risk Assessment Form -->


<!-- PHP code -->
<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Ensure user is logged in
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
        'title' => 'GlucoPredictor Risk Assessment',
        'form' => 'Diabetes Risk Assessment Form',
        'age' => 'Age',
        'enter_age' => 'Enter Your Age',
        'gender' => 'Gender',
        'male' => 'Male',
        'female' => 'Female',
        'pregnancies' => 'Number of Pregnancies',
        'enter_pregnancies' => 'Enter No. of Pregnancies (if any), or 0 if none',
        'height' => 'Height',
        'enter_height' => 'cm',
        'weight' => 'Weight',
        'enter_weight' => 'kg',
        'bmi' => 'BMI',
        'auto_calculated' => 'Automatically Calculated',
        'family_history' => 'Do you have a family history of diabetes?',
        'yes' => 'Yes',
        'no' => 'No',
        'high_bp' => 'Have you ever been diagnosed with high blood pressure?',
        'exercise' => 'How often do you exercise?',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'rarely' => 'Rarely',
        'never' => 'Never',
        'sugar_intake' => 'How often do you consume sugary foods or drinks?',
        'multiple_day' => 'Multiple times a day',
        'once_day' => 'Once a day',
        'few_weekly' => 'A few times a week',
        'rarely_never' => 'Rarely or never',
        'symptoms' => 'Are you experiencing any of these symptoms? (Optional)',
        'weight_loss' => 'Weight Loss',
        'blurry_vision' => 'Blurred Vision',
        'excessive_thirst' => 'Excessive Thirst',
        'frequent_urination' => 'Frequent Urination',
        'fatigue' => 'Fatigue',
        'calculate_risk' => 'Calculate Risk Score',
        'disclaimer' => 'Disclaimer: This assessment is for informational purposes only and should not be considered medical advice.',
    ],

    'ms' => [
        'title' => 'Penilaian Risiko GlucoPredictor',
        'form' => 'Borang Penilaian Risiko Diabetes',
        'age' => 'Umur',
        'enter_age' => 'Masukkan Umur Anda',
        'gender' => 'Jantina',
        'male' => 'Lelaki',
        'female' => 'Perempuan',
        'pregnancies' => 'Bilangan Kehamilan',
        'enter_pregnancies' => 'Masukkan Bil. Kehamilan (jika ada), atau 0 jika tiada',
        'height' => 'Tinggi',
        'enter_height' => 'cm',
        'weight' => 'Berat',
        'enter_weight' => 'kg',
        'bmi' => 'BMI',
        'auto_calculated' => 'Dikira Secara Automatik',
        'family_history' => 'Adakah anda mempunyai sejarah keluarga menghidap diabetes?',
        'yes' => 'Ya',
        'no' => 'Tidak',
        'high_bp' => 'Adakah anda pernah didiagnosis dengan tekanan darah tinggi?',
        'exercise' => 'Seberapa kerap anda bersenam?',
        'daily' => 'Setiap hari',
        'weekly' => 'Setiap minggu',
        'rarely' => 'Jarang',
        'never' => 'Tidak pernah',
        'sugar_intake' => 'Seberapa kerap anda mengambil makanan atau minuman manis?',
        'multiple_day' => 'Beberapa kali sehari',
        'once_day' => 'Sekali sehari',
        'few_weekly' => 'Beberapa kali seminggu',
        'rarely_never' => 'Jarang atau tidak pernah',
        'symptoms' => 'Adakah anda mengalami mana-mana simptom berikut? (Pilihan)',
        'weight_loss' => 'Penurunan Berat Badan',
        'blurry_vision' => 'Penglihatan Kabur',
        'excessive_thirst' => 'Dahaga Berlebihan',
        'frequent_urination' => 'Kekerapan Kencing',
        'fatigue' => 'Keletihan',
        'calculate_risk' => 'Kira Skor Risiko',
        'disclaimer' => 'Penafian: Penilaian ini hanya untuk tujuan maklumat dan tidak boleh dianggap sebagai nasihat perubatan.',
    ],
];

include("../database.php");

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CSRF Protection: Check token validity
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Use user_id from session, not from form input
    $user_id = $_SESSION["user_id"];

    // Validate inputs
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 120]]);
    $gender = in_array($_POST['gender'], ['male', 'female']) ? $_POST['gender'] : null;
    $pregnancies = filter_input(INPUT_POST, 'pregnancies', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    $height = filter_input(INPUT_POST, 'height_input', FILTER_VALIDATE_FLOAT);
    $weight = filter_input(INPUT_POST, 'weight_input', FILTER_VALIDATE_FLOAT);
    $bmi = filter_input(INPUT_POST, 'bmi', FILTER_VALIDATE_FLOAT);
    $family_history = in_array($_POST['family_history'], ['yes', 'no']) ? $_POST['family_history'] : null;
    $high_bp = in_array($_POST['high_bp'], ['yes', 'no']) ? $_POST['high_bp'] : null;
    $activity = in_array($_POST['activity'], ['daily', 'weekly', 'rarely', 'never']) ? $_POST['activity'] : null;
    $sugar_intake = in_array($_POST['sugar_intake'], ['Every-meal', 'Once-daily', 'Few-weekly', 'Rarely-never']) ? $_POST['sugar_intake'] : null;

    // Handle symptoms array safely
    $allowed_symptoms = ['Weight_loss', 'Blurry_vision', 'Excessive_thirst', 'Frequent_urination', 'Fatigue'];
    $symptoms = isset($_POST['symptoms']) && is_array($_POST['symptoms'])
        ? array_intersect($_POST['symptoms'], $allowed_symptoms)
        : [];

    $symptoms_str = !empty($symptoms) ? implode(",", $symptoms) : null;

    // Insert into database using prepared statements
    $stmt = $conn->prepare("INSERT INTO assessment 
    (user_id, age, gender, pregnancies, height_input, weight_input, bmi, family_history, high_bp, activity, sugar_intake, symptoms) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("iisdssssssss", $user_id, $age, $gender, $pregnancies, $height, $weight, $bmi, $family_history, $high_bp, $activity, $sugar_intake, $symptoms_str);


    // Convert categorical inputs to numerical values
    $gender_numeric = ($gender === 'female') ? 1 : 0;
    $family_history_numeric = ($family_history === 'yes') ? 1 : 0;
    $high_bp_numeric = ($high_bp === 'yes') ? 1 : 0;

    // Map exercise frequency
    $activity_mapping = ['never' => 0, 'rarely' => 1, 'weekly' => 2, 'daily' => 3];
    $activity_numeric = $activity_mapping[$activity] ?? null;

    // Map sugar intake
    $sugar_intake_mapping = [
        'Every-meal' => 3,
        'Once-daily' => 2,
        'Few-weekly' => 1,
        'Rarely-never' => 0
    ];
    $sugar_intake_numeric = $sugar_intake_mapping[$sugar_intake] ?? null;



    if ($activity_numeric === null || $sugar_intake_numeric === null) {
        die(json_encode(["error" => "Invalid activity or sugar intake value"]));
    }

    // Prepare data to send to predict.py
    $data = [
        "age" => $age,
        "gender" => $gender_numeric,
        "pregnancies" => is_numeric($pregnancies) ? (int)$pregnancies : 0,
        "bmi" => $bmi,
        "family_history" => $family_history_numeric,
        "high_bp" => $high_bp_numeric,
        "activity" => $activity_numeric,
        "sugar_intake" => $sugar_intake_numeric,
    ];



    // Convert to JSON and send to Python script
    $json_data = json_encode($data);



    $command = "python " . realpath(__DIR__ . "/../ml/predict.py");
    $descriptorspec = [
        0 => ["pipe", "r"],  // STDIN for Python (Sends data)
        1 => ["pipe", "w"],  // STDOUT from Python (Reads data)
        2 => ["pipe", "w"]   // STDERR from Python (Reads errors)
    ];

    $process = proc_open($command, $descriptorspec, $pipes); // Open process to run Python script

    if (is_resource($process)) { // Check if process opened successfully
        fwrite($pipes[0], $json_data); // Send JSON data to Python script
        fclose($pipes[0]); // Close STDIN

        $output = stream_get_contents($pipes[1]); // Read output from Python script
        fclose($pipes[1]); // Close STDOUT

        $error_output = stream_get_contents($pipes[2]); // Read error output from Python script
        fclose($pipes[2]);  // Close STDERR

        proc_close($process); // Close the process
    }


    $prediction_result = json_decode($output, true); // Decode JSON output


    // Store AI prediction in session
    if (isset($prediction_result['prediction']) && isset($prediction_result['ai_tips'])) {
        $_SESSION['ai_prediction'] = $prediction_result['prediction'];
        $_SESSION['ai_tips'] = $prediction_result['ai_tips'];
    } else {
        $_SESSION['ai_prediction'] = null;
        $_SESSION['ai_tips'] = "No bonus AI-based tip available.";
    }


    // Function to generate health tips based on user inputs
    function generateHealthTips($user_id, $age, $gender, $pregnancies, $bmi, $family_history, $high_bp, $activity, $sugar_intake, $symptoms)

    {
        $tips = [];

        // 🏆 Age-Based Tips
        if ($age <= 25) {
            $tips[] = [
                'en' => "🎯 Prime years! Build lifelong habits now. Avoid sugary drinks and processed snacks to prevent future insulin resistance. Prioritize balanced meals and exercise. 💪🚴‍♂️",
                'ms' => "🎯 Umur terbaik! Bina tabiat sihat sepanjang hayat sekarang. Elakkan minuman bergula dan makanan diproses untuk mencegah rintangan insulin. Utamakan pemakanan seimbang dan senaman. 💪🚴‍♂️"
            ];
        } elseif ($age <= 34) {
            $tips[] = [
                'en' => "⚡ Take control! Schedule annual check-ups to monitor blood sugar, cholesterol, and blood pressure. Focus on nutrition and exercise to maintain a healthy weight. 🏥✅",
                'ms' => "⚡ Ambil kawalan! Jadualkan pemeriksaan tahunan untuk memantau paras gula darah, kolesterol dan tekanan darah. Fokus kepada pemakanan dan senaman untuk mengekalkan berat badan yang sihat. 🏥✅"
            ];
        } elseif ($age <= 44) {
            $tips[] = [
                'en' => "⏳ As metabolism slows, focus on portion control and mindful eating. Regular exercise helps maintain muscle mass and metabolism. 🥗🏋️‍♀️",
                'ms' => "⏳ Apabila metabolisme menjadi perlahan, beri tumpuan pada kawalan saiz hidangan dan pemakanan berhemah. Senaman tetap membantu mengekalkan jisim otot dan metabolisme. 🥗🏋️‍♀️"
            ];
        } elseif ($age <= 54) {
            $tips[] = [
                'en' => "💡 Your body’s sugar processing is changing. Adjust with healthier eating, reducing refined sugars, and staying active. 🏃‍♀️🍎",
                'ms' => "💡 Cara badan anda memproses gula sedang berubah. Sesuaikan dengan pemakanan lebih sihat, kurangkan gula halus dan kekal aktif. 🏃‍♀️🍎"
            ];
        } elseif ($age <= 64) {
            $tips[] = [
                'en' => "⚠️ At higher risk for diabetes. Monitor blood sugar levels and track your food intake. Stay active with regular exercise and strength training. 🏋️‍♀️🥜",
                'ms' => "⚠️ Risiko diabetes meningkat. Pantau paras gula dalam darah dan catat pemakanan anda. Kekal aktif dengan senaman dan latihan kekuatan secara berkala. 🏋️‍♀️🥜"
            ];
        } else {
            $tips[] = [
                'en' => "🔎 Seniors face the highest diabetes risks. Regular check-ups and daily physical activity, like walking or light exercises, are vital. 🚶‍♀️🏥",
                'ms' => "🔎 Warga emas menghadapi risiko diabetes tertinggi. Pemeriksaan berkala dan aktiviti fizikal harian seperti berjalan kaki atau senaman ringan adalah penting. 🚶‍♀️🏥"
            ];
        }


        // 🚹🚺 Gender-Based Tips
        if ($gender === "male") {
            $tips[] = [
                'en' => "🚹🧑 Men have a higher risk of diabetes, often due to increased visceral fat and lower insulin sensitivity. Limit alcohol and smoking to reduce risk factors. 🚫🥤🚬",
                'ms' => "🚹🧑 Lelaki mempunyai risiko diabetes yang lebih tinggi, selalunya disebabkan oleh lemak organ yang lebih banyak dan sensitiviti insulin yang lebih rendah. Hadkan pengambilan alkohol dan merokok untuk kurangkan risiko. 🚫🥤🚬"
            ];
        } else {
            $tips[] = [
                'en' => "🚺👩‍⚕️ Women, particularly those with a history of gestational diabetes, should monitor blood sugar levels regularly. Gestational diabetes increases the risk of developing type 2 diabetes later in life. 🍎💖",
                'ms' => "🚺👩‍⚕️ Wanita, terutamanya yang pernah mengalami diabetes semasa hamil, perlu memantau paras gula dalam darah secara berkala. Diabetes kehamilan meningkatkan risiko mendapat diabetes jenis 2 pada masa akan datang. 🍎💖"
            ];
        }


        // 🤰 Pregnancy-Based Tips (For Females)
        if ($gender === "female" && is_numeric($pregnancies) && $pregnancies > 0) {
            if ($pregnancies == 1) {
                $tips[] = [
                    'en' => "🤰👶 A single pregnancy increases diabetes risk due to hormonal changes. Focus on lean proteins, whole grains, and fiber. Exercise daily like walking or prenatal yoga. 🥗🚶‍♀️🧘‍♂️",
                    'ms' => "🤰👶 Kehamilan tunggal meningkatkan risiko diabetes kerana perubahan hormon. Fokus pada protein tanpa lemak, bijirin penuh, dan serat. Senaman harian seperti berjalan kaki atau yoga pralahir. 🥗🚶‍♀️🧘‍♂️"
                ];
            } elseif ($pregnancies == 2) {
                $tips[] = [
                    'en' => "🤰🔎 Two pregnancies raise diabetes risk. Monitor sugar levels and eat a low-GI diet. Avoid refined carbs and stay active with strength workouts. 🍞💪",
                    'ms' => "🤰🔎 Dua kehamilan meningkatkan risiko diabetes. Pantau paras gula dan makan diet berindeks gula rendah. Elakkan karbohidrat halus dan kekal aktif dengan senaman kekuatan. 🍞💪"
                ];
            } elseif ($pregnancies >= 3) {
                $tips[] = [
                    'en' => "🤰⚠️ Multiple pregnancies heighten diabetes risk. Eat high-fiber foods, lean proteins, and healthy fats. Stay active with swimming or resistance training. 🥦🏋️‍♂️",
                    'ms' => "🤰⚠️ Banyak kehamilan meningkatkan risiko diabetes. Makan makanan tinggi serat, protein tanpa lemak, dan lemak sihat. Kekal aktif dengan berenang atau latihan rintangan. 🥦🏋️‍♂️"
                ];
            }
        } else {
            $tips[] = [
                'en' => "🌿✅ No pregnancy-related risks. Maintain a healthy lifestyle with balanced meals and daily movement. 🥗💪",
                'ms' => "🌿✅ Tiada risiko berkaitan kehamilan. Kekalkan gaya hidup sihat dengan pemakanan seimbang dan pergerakan harian. 🥗💪"
            ];
        }


        // 📊 BMI-Based Tips
        if ($bmi < 18.5) {
            $tips[] = [
                'en' => "⚠️ Underweight can lead to unstable blood sugar. Focus on calorie-dense, nutrient-rich foods such as avocados, nuts, lean proteins, and include resistance exercises to build muscle. 🥑🥜🏋️‍♂️",
                'ms' => "⚠️ Berat badan rendah boleh menyebabkan gula darah yang tidak stabil. Fokus pada makanan yang kaya kalori dan nutrien seperti alpukat, kacang, protein tanpa lemak, dan sertakan latihan ketahanan untuk membina otot. 🥑🥜🏋️‍♂️"
            ];
        } elseif ($bmi < 24.9) {
            $tips[] = [
                'en' => "✅ Healthy BMI! Maintain it with a balanced diet (lean proteins, whole grains, healthy fats) and 2 hours and 30 minutes of exercise weekly. Stay hydrated and manage stress. 🚴‍♂️💪",
                'ms' => "✅ BMI Sihat! Kekalkan dengan diet seimbang (protein tanpa lemak, bijirin penuh, lemak sihat) dan 2 jam 30 minit senaman setiap minggu. Kekal terhidrat dan uruskan tekanan. 🚴‍♂️💪"
            ];
        } elseif ($bmi < 29.9) {
            $tips[] = [
                'en' => "⚠️ Slightly overweight! Reduce diabetes risk with small changes like walk after meals, control portions, and choose healthier snacks. Try high-protein breakfasts. 🍽️🚶‍♂️🥚",
                'ms' => "⚠️ Sedikit berlebihan berat badan! Kurangkan risiko diabetes dengan perubahan kecil seperti berjalan selepas makan, kawal saiz hidangan, dan pilih snek yang lebih sihat. Cuba sarapan tinggi protein. 🍽️🚶‍♂️🥚"
            ];
        } elseif ($bmi < 34.9) {
            $tips[] = [
                'en' => "⚠️ Obesity increases diabetes risk. Cut refined carbs and focus on fiber-rich foods. Exercise 30 minutes daily, including cardio and strength training. 🥦🍎💪",
                'ms' => "⚠️ Obesiti meningkatkan risiko diabetes. Kurangkan karbohidrat halus dan fokus pada makanan tinggi serat. Lakukan senaman 30 minit setiap hari, termasuk kardio dan latihan kekuatan. 🥦🍎💪"
            ];
        } elseif ($bmi < 39.9) {
            $tips[] = [
                'en' => "🚨 High obesity risk! Meal prep, swap processed foods for whole meals, and include strength training and low-impact exercises like swimming. 🍠🥗🏊‍♀️",
                'ms' => "🚨 Risiko obesiti tinggi! Sediakan makanan lebih awal, tukar makanan proses dengan hidangan penuh, dan sertakan latihan kekuatan serta senaman berimpak rendah seperti berenang. 🍠🥗🏊‍♀️"
            ];
        } else {
            $tips[] = [
                'en' => "🔴 Severe obesity may need medical supervision. Consult a doctor for a personalized plan and gradual changes to improve health. 🏥🩺",
                'ms' => "🔴 Obesiti tinggi mungkin memerlukan pengawasan perubatan. Rujuk doktor untuk pelan peribadi dan perubahan berperingkat untuk meningkatkan kesihatan. 🏥🩺"
            ];
        }


        // 🧬 Family History
        if ($family_history === "yes") {
            $tips[] = [
                'en' => "🧬 Family history of diabetes increases your risk, but you can take proactive steps to stay healthy! 💪",
                'ms' => "🧬 Sejarah keluarga dengan diabetes meningkatkan risiko anda, tetapi anda boleh mengambil langkah proaktif untuk kekal sihat! 💪"
            ];
        } else {
            $tips[] = [
                'en' => "🎉 Lower genetic risk, but lifestyle choices are key to maintaining health! 🌱",
                'ms' => "🎉 Risiko genetik yang lebih rendah, tetapi pilihan gaya hidup adalah kunci untuk mengekalkan kesihatan! 🌱"
            ];
        }


        // ❤️ High Blood Pressure
        if ($high_bp === "yes") {
            $tips[] = [
                'en' => "⚠️ High blood pressure raises heart disease risk. Reduce salt, eat potassium-rich foods, hydrate, and exercise 30 minutes daily. Monitor blood pressure level and manage stress. 🧘‍♀️",
                'ms' => "⚠️ Tekanan darah tinggi meningkatkan risiko penyakit jantung. Kurangkan garam, makan makanan kaya kalium, pastikan badan terhidrat, dan bersenam 30 minit setiap hari. Pantau paras tekanan darah dan uruskan stres. 🧘‍♀️"
            ];
        } else {
            $tips[] = [
                'en' => "✅ Keep your heart healthy with a low-sodium, nutrient-rich diet, regular exercise, and staying hydrated. 💧💖",
                'ms' => "✅ Kekalkan kesihatan jantung dengan diet rendah natrium, makanan kaya nutrien, senaman berkala, dan pastikan badan terhidrat. 💧💖"
            ];
        }


        // 🏃 Activity Level
        $activityTips = [
            "daily" => [
                'en' => "🎉 Daily exercise boosts blood sugar control, insulin sensitivity, and heart health. Mix aerobic exercises (walking, cycling) with strength training. 🏋️‍♂️✅",
                'ms' => "🎉 Senaman harian meningkatkan kawalan gula darah, sensitiviti insulin, dan kesihatan jantung. Campurkan senaman aerobik (berjalan, berbasikal) dengan latihan kekuatan. 🏋️‍♂️✅"
            ],
            "weekly" => [
                'en' => "👍 Exercising weekly is great! Add light activities daily like stretches, stairs, or standing breaks. Aim for 2 hours 30 minutes of moderate exercise weekly. 🚶‍♂️🏃‍♂️",
                'ms' => "👍 Bersenam setiap minggu adalah hebat! Tambah aktiviti ringan setiap hari seperti regangan, tangga, atau berehat berdiri. Sasarkan 2 jam 30 minit senaman sederhana setiap minggu. 🚶‍♂️🏃‍♂️"
            ],
            "rarely" => [
                'en' => "⚠️ A sedentary lifestyle raises diabetes risk. Start with 10-minute walks and home workouts like squats or chair yoga. 🚶‍♀️🏋️‍♂️ Keep it fun with dancing or cycling! 🎵💃🚴",
                'ms' => "⚠️ Gaya hidup sedentari meningkatkan risiko diabetes. Mulakan dengan berjalan 10 minit dan senaman di rumah seperti squats atau yoga kerusi. 🚶‍♀️🏋️‍♂️ Jadikan ia menyeronokkan dengan menari atau berbasikal! 🎵💃🚴"
            ],
            "never" => [
                'en' => "🚨 Lack of activity raises diabetes risk. Start with light stretches or short walks. Gradually build up to 15-30 minutes daily for big health improvements! 🏡💪",
                'ms' => "🚨 Kekurangan aktiviti meningkatkan risiko diabetes. Mulakan dengan regangan ringan atau berjalan pendek. Secara beransur-ansur tingkatkan hingga 15-30 minit setiap hari untuk peningkatan kesihatan yang besar! 🏡💪"
            ]
        ];

        if (isset($activityTips[$activity])) {
            $tips[] = $activityTips[$activity];
        }


        // 🍬 Sugar Intake
        $sugarIntakeTips = [
            "Every-meal" => [
                'en' => "🚨 High sugar intake spikes blood sugar, leading to insulin resistance. Cut down sugary drinks and processed snacks, opt for whole fruits, nuts, and fiber-rich foods like whole grains. 🍬🚫🍞🥗",
                'ms' => "🚨 Pengambilan gula yang tinggi meningkatkan gula darah, menyebabkan rintangan insulin. Kurangkan minuman bergula dan snek proses, pilih buah-buahan keseluruhan, kacang, dan makanan kaya serat seperti bijirin penuh. 🍬🚫🍞🥗"
            ],
            "Once-daily" => [
                'en' => "⚠️ Daily sugar may contribute to insulin resistance. Replace desserts with dark chocolate, berries, or cinnamon-spiced nuts. Keep added sugar under 25 grams per day. 🍫🥜✅",
                'ms' => "⚠️ Gula harian mungkin menyumbang kepada rintangan insulin. Gantikan pencuci mulut dengan coklat gelap, beri, atau kacang dengan rempah kayu manis. Kekalkan pengambilan gula tambahan di bawah 25 gram sehari. 🍫🥜✅"
            ],
            "Few-weekly" => [
                'en' => "✅ Great job on moderate sugar intake! Pair sugary treats with fiber and protein like fruit with yogurt and stick to low-GI options like apples and oats. 🍎🥗🍏",
                'ms' => "✅ Kerja hebat dengan pengambilan gula yang sederhana! Pasangkan makanan manis dengan serat dan protein seperti buah dengan yogurt dan pilih pilihan GI rendah seperti epal dan oat. 🍎🥗🍏"
            ],
            "Rarely-never" => [
                'en' => "🎉 Excellent discipline! Low sugar intake reduces diabetes risk and supports metabolic health. Try natural sweeteners and focus on whole, nutrient-dense foods. 🥑🥜🌱",
                'ms' => "🎉 Disiplin yang cemerlang! Pengambilan gula rendah mengurangkan risiko diabetes dan menyokong kesihatan metabolik. Cuba pemanis semulajadi dan tumpukan pada makanan keseluruhan yang kaya nutrien. 🥑🥜🌱"
            ]
        ];

        if (isset($sugarIntakeTips[$sugar_intake])) {
            $tips[] = $sugarIntakeTips[$sugar_intake];
        }


        // 🔎 Symptom-Based Tips
        $symptomTips = [
            "Weight_loss" => [
                'en' => "⚠️ Unintentional weight loss may signal high blood sugar. Monitor glucose, eat healthy fats (avocados, nuts), and consult a doctor if it persists. 🏥🥑🍳",
                'ms' => "⚠️ Penurunan berat badan yang tidak disengajakan mungkin menandakan gula darah tinggi. Pantau glukosa, makan lemak sihat (alpukat, kacang), dan rujuk doktor jika ia berterusan. 🏥🥑🍳"
            ],
            "Blurry_vision" => [
                'en' => "👀 Blurred vision could be caused by fluctuating blood sugar. Get an eye test if symptoms continue. 🔍",
                'ms' => "👀 Penglihatan kabur mungkin disebabkan oleh gula darah yang tidak stabil. Dapatkan ujian mata jika simptom berterusan. 🔍"
            ],
            "Excessive_thirst" => [
                'en' => "💧 Extreme thirst may be due to high blood sugar. Stay hydrated with water and herbal teas, and include electrolyte-rich foods like bananas and greens. 💦🍌🥬",
                'ms' => "💧 Dahaga yang melampau mungkin disebabkan oleh gula darah tinggi. Kekal terhidrat dengan air dan teh herba, serta sertakan makanan kaya elektrolit seperti pisang dan sayur-sayuran hijau. 💦🍌🥬"
            ],
            "Frequent_urination" => [
                'en' => "🚽 Frequent urination may indicate high glucose levels. Monitor hydration, get a glucose test, and consult a doctor if persistent. 🩸",
                'ms' => "🚽 Kekerapan kencing mungkin menunjukkan paras glukosa yang tinggi. Pantau hidrasi, lakukan ujian glukosa, dan rujuk doktor jika berterusan. 🩸"
            ],
            "Fatigue" => [
                'en' => "😴 Fatigue may be from blood sugar fluctuations. Eat steady energy sources (complex carbs, lean proteins) and get regular light exercise. 🏃‍♂️🍚",
                'ms' => "😴 Keletihan mungkin disebabkan oleh turun naik gula darah. Makan sumber tenaga yang stabil (karbohidrat kompleks, protein tanpa lemak) dan lakukan senaman ringan secara berkala. 🏃‍♂️🍚"
            ]
        ];

        foreach ($symptoms as $symptom) {
            if (isset($symptomTips[$symptom])) {
                $tips[] = $symptomTips[$symptom];
            }
        }

        return $tips;
    }

    // Generate and store health tips in the session
    $_SESSION['health_tips'][$user_id] = generateHealthTips($user_id, $age, $gender, $pregnancies, $bmi, $family_history, $high_bp, $activity, $sugar_intake, $symptoms);

    if ($stmt->execute()) {
        header("Location: result.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>


<!-- HTML code -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title><?php echo $translations[$lang]['title']; ?></title>
    <link rel="stylesheet" href="/glucopredictor/style.css">
    <link rel="shortcut icon" href="images/logo-favicon.png">
</head>

<body>
    <section class="container-box">
        <h1><span>Gluco</span>Predictor</h1>
        <h4><?php echo $translations[$lang]['form']; ?></h4>
        <div class="lang-box" style="text-align: center;">
            <a href="?lang=en">EN</a> | <a href="?lang=ms">BM</a>
        </div>
        <form action="form.php" method="post" class="form" id="riskForm">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>" />
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
            <div class="input-box">
                <label for="age"><?php echo $translations[$lang]['age']; ?></label>
                <input type="number" id="age" name="age" placeholder="<?php echo $translations[$lang]['enter_age']; ?>" min="0" max="120" required />
            </div>

            <div class="gender-box">
                <h3><?php echo $translations[$lang]['gender']; ?></h3>
                <div class="gender-option">
                    <div class="gender">
                        <input type="radio" id="male" name="gender" value="male" onclick="togglePregnancyInput()" required />
                        <label for="male"><?php echo $translations[$lang]['male']; ?></label>
                    </div>
                    <div class="gender">
                        <input type="radio" id="female" name="gender" value="female" onclick="togglePregnancyInput()" required />
                        <label for="female"><?php echo $translations[$lang]['female']; ?></label>
                    </div>
                </div>
            </div>

            <div class="input-box">
                <label for="pregnancies"><?php echo $translations[$lang]['pregnancies']; ?></label>
                <input type="number" id="pregnancies" name="pregnancies" placeholder="<?php echo $translations[$lang]['enter_pregnancies']; ?>" min="0" disabled />
            </div>

            <div class="column">
                <div class="input-box">
                    <label for="height"><?php echo $translations[$lang]['height']; ?></label>
                    <input type="number" id="height" name="height_input" placeholder="<?php echo $translations[$lang]['enter_height']; ?>" min="0" max="300" set="any" oninput="calculateBMI()" required />
                </div>

                <div class="input-box">
                    <label for="weight"><?php echo $translations[$lang]['weight']; ?></label>
                    <input type="number" id="weight" name="weight_input" placeholder="<?php echo $translations[$lang]['enter_weight']; ?>" min="0" max="500" set="any" oninput="calculateBMI()" required />
                </div>

                <div class="input-box">
                    <label for="bmi"><?php echo $translations[$lang]['bmi']; ?></label>
                    <input type="text" id="bmi" name="bmi" placeholder="<?php echo $translations[$lang]['auto_calculated']; ?>" style="cursor: default" readonly />
                </div>
            </div>

            <div class="family-box">
                <h3><?php echo $translations[$lang]['family_history']; ?></h3>
                <div class="family-option">
                    <div class="family">
                        <input type="radio" id="family-yes" name="family_history" value="yes" required />
                        <label for="family-yes"><?php echo $translations[$lang]['yes']; ?></label>
                    </div>

                    <div class="family">
                        <input type="radio" id="family-no" name="family_history" value="no" required />
                        <label for="family-no"><?php echo $translations[$lang]['no']; ?></label>
                    </div>
                </div>
            </div>

            <div class="bp-box">
                <h3><?php echo $translations[$lang]['high_bp']; ?></h3>
                <div class="bp-option">
                    <div class="bp">
                        <input type="radio" id="bp-yes" name="high_bp" value="yes" required />
                        <label for="bp-yes"><?php echo $translations[$lang]['yes']; ?></label>
                    </div>

                    <div class="bp">
                        <input type="radio" id="bp-no" name="high_bp" value="no" required />
                        <label for="bp-no"><?php echo $translations[$lang]['no']; ?></label>
                    </div>
                </div>
            </div>

            <div class="activity-box">
                <h3><?php echo $translations[$lang]['exercise']; ?></h3>
                <div class="activity-option">
                    <div class="activity">
                        <input type="radio" id="activity-daily" name="activity" value="daily" required />
                        <label for="activity-daily"><?php echo $translations[$lang]['daily']; ?></label>
                    </div>

                    <div class="activity">
                        <input type="radio" id="activity-weekly" name="activity" value="weekly" required />
                        <label for="activity-weekly"><?php echo $translations[$lang]['weekly']; ?></label>
                    </div>

                    <div class="activity">
                        <input type="radio" id="activity-rarely" name="activity" value="rarely" required />
                        <label for="activity-rarely"><?php echo $translations[$lang]['rarely']; ?></label>
                    </div>

                    <div class="activity">
                        <input type="radio" id="activity-never" name="activity" value="never" required />
                        <label for="activity-never"><?php echo $translations[$lang]['never']; ?></label>
                    </div>
                </div>
            </div>

            <div class="sugar-box">
                <h3><?php echo $translations[$lang]['sugar_intake']; ?></h3>
                <div class="sugar-option">
                    <div class="sugar">
                        <input type="radio" id="every-meal" name="sugar_intake" value="Every-meal" required />
                        <label for="every-meal"><?php echo $translations[$lang]['multiple_day']; ?></label>
                    </div>
                    <div class="sugar">
                        <input type="radio" id="once-daily" name="sugar_intake" value="Once-daily" required />
                        <label for="once-daily"><?php echo $translations[$lang]['once_day']; ?></label>
                    </div>
                    <div class="sugar">
                        <input type="radio" id="few-weekly" name="sugar_intake" value="Few-weekly" required />
                        <label for="few-weekly"><?php echo $translations[$lang]['few_weekly']; ?></label>
                    </div>
                    <div class="sugar">
                        <input type="radio" id="rarely-never" name="sugar_intake" value="Rarely-never" required />
                        <label for="rarely-never"><?php echo $translations[$lang]['rarely_never']; ?></label>
                    </div>
                </div>
            </div>

            <div class="symptom-box">
                <h3><?php echo $translations[$lang]['symptoms']; ?></h3>
                <div class="symptom-option">
                    <div class="symptom">
                        <input type="checkbox" id="weight_loss" name="symptoms[]" value="Weight_loss" />
                        <label for="weight_loss"><?php echo $translations[$lang]['weight_loss']; ?></label>
                    </div>

                    <div class="symptom">
                        <input type="checkbox" id="blurry_vision" name="symptoms[]" value="Blurry_vision" />
                        <label for="blurry_vision"><?php echo $translations[$lang]['blurry_vision']; ?></label>
                    </div>

                    <div class="symptom">
                        <input type="checkbox" id="excessive_thirst" name="symptoms[]" value="Excessive_thirst" />
                        <label for="excessive_thirst"><?php echo $translations[$lang]['excessive_thirst']; ?></label>
                    </div>

                    <div class="symptom">
                        <input type="checkbox" id="frequent_urination" name="symptoms[]" value="Frequent_urination" />
                        <label for="frequent_urination"><?php echo $translations[$lang]['frequent_urination']; ?></label>
                    </div>

                    <div class="symptom">
                        <input type="checkbox" id="fatigue" name="symptoms[]" value="Fatigue" />
                        <label for="fatigue"><?php echo $translations[$lang]['fatigue']; ?></label>
                    </div>
                </div>
            </div>

            <button type="submit" onclick="calculateRiskScore()"><?php echo $translations[$lang]['calculate_risk']; ?></button>
        </form>
        <h6 class="disclaimer"><?php echo $translations[$lang]['disclaimer']; ?></h6>
    </section>


    <!-- JavaScript code -->
    <script>
        function togglePregnancyInput() {
            const isFemale = document.getElementById('female').checked;
            const pregnanciesInput = document.getElementById('pregnancies');

            if (isFemale) {
                pregnanciesInput.disabled = false;
                pregnanciesInput.required = true;
            } else {
                pregnanciesInput.disabled = true;
                pregnanciesInput.value = ''; // Clear value
                pregnanciesInput.required = false;
            }
        }

        function calculateBMI() {
            const height = parseFloat(document.getElementById('height').value);
            const weight = parseFloat(document.getElementById('weight').value);
            const bmiInput = document.getElementById('bmi');

            if (height > 0 && weight > 0) {
                const heightInMeters = height / 100; // Convert height to meters
                const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(2);
                bmiInput.value = bmi;
            } else {
                bmiInput.value = ''; // Clear BMI field if inputs are invalid
            }
        }

        function calculateRiskScore() {
            let riskScore = 0;

            // Age Factor (Higher risk as age increases)
            const age = parseInt(document.getElementById("age").value, 10) || 0;
            if (age > 0) {
                if (age <= 25) riskScore += 0; // Young, lowest risk
                else if (age <= 34) riskScore += 2;
                else if (age <= 44) riskScore += 4;
                else if (age <= 54) riskScore += 6;
                else if (age <= 64) riskScore += 8;
                else riskScore += 10; // 65+ highest risk
            }

            // Gender
            const gender = document.querySelector('input[name="gender"]:checked');

            // Pregnancy Factor (Only for Females)
            const pregnancies = parseInt(document.getElementById("pregnancies").value, 10) || 0;
            if (gender?.id === "female") {
                if (pregnancies >= 3) riskScore += 6; // High risk for gestational diabetes
                else if (pregnancies === 2) riskScore += 4;
                else if (pregnancies === 1) riskScore += 2;
            }

            // BMI Risk (Medical weight classifications)
            const bmi = parseFloat(document.getElementById("bmi").value) || 0;
            if (bmi > 0) {
                if (bmi < 18.5) riskScore += 1; // Underweight
                else if (bmi < 24.9) riskScore += 0; // Normal weight
                else if (bmi < 29.9) riskScore += 4; // Overweight
                else if (bmi < 34.9) riskScore += 6; // Obesity I
                else if (bmi < 39.9) riskScore += 8; // Obesity II
                else riskScore += 10; // Severe obesity III
            }

            // Family History of Diabetes (Strong genetic factor)
            const familyHistory = document.querySelector('input[name="family"]:checked');
            if (familyHistory?.id === "family-yes") riskScore += 6;

            // High Blood Pressure (Strong correlation with diabetes)
            const highBP = document.querySelector('input[name="bp"]:checked');
            if (highBP?.id === "bp-yes") riskScore += 5;

            // Physical Activity Risk (Sedentary lifestyle increases diabetes risk)
            const activity = document.querySelector('input[name="activity"]:checked');
            if (activity) {
                if (activity.id === "activity-daily") riskScore += 0; // Active lifestyle
                else if (activity.id === "activity-weekly") riskScore += 3;
                else if (activity.id === "activity-rarely") riskScore += 6;
                else if (activity.id === "activity-never") riskScore += 10; // Very high risk
            }

            // Sugar Intake Risk (More refined scoring)
            const sugar = document.querySelector('input[name="sugar"]:checked');
            if (sugar) {
                if (sugar.id === "every-meal") riskScore += 8; // Very high risk due to high sugar consumption
                else if (sugar.id === "once-daily") riskScore += 5;
                else if (sugar.id === "few-weekly") riskScore += 2;
                else if (sugar.id === "rarely-never") riskScore += 0; // Low risk
            }

            // Symptoms Count (Each symptom adds risk)
            const symptoms = document.querySelectorAll('input[name="symptoms"]:checked');
            riskScore += symptoms.length * 1; // Stronger impact of symptoms

            // Debugging: Log all values
            console.log("Final Score:", riskScore);

            // Risk Level Classification (More medically accurate)
            let riskLevel = '';
            if (riskScore <= 10) riskLevel = 'Low Risk'; // Lower scores indicate low risk
            else if (riskScore <= 20) riskLevel = 'Moderate Risk'; // Medium scores
            else riskLevel = 'High Risk'; // High scores

            // Store in sessionStorage
            sessionStorage.setItem('riskScore', riskScore);
            sessionStorage.setItem('riskLevel', riskLevel);

        }
    </script>


</body>


</html>