import sys
import json
import numpy as np
import xgboost as xgb
import os

try:
    # Get absolute path to model
    script_dir = os.path.dirname(os.path.abspath(__file__))
    model_path = os.path.join(script_dir, "xgboost_diabetes_model.json")

    # Ensure model file exists
    if not os.path.exists(model_path):
        raise FileNotFoundError(f"Model file not found: {model_path}")

    # Load XGBoost model
    model = xgb.Booster()
    model.load_model(model_path)


    # Read input from PHP
    input_json = sys.stdin.read().strip()
    
    if not input_json:
        raise ValueError("No input data received from PHP.")


    # Parse JSON input
    try:
        input_data = json.loads(input_json)
    except json.JSONDecodeError as e:
        raise ValueError(f"Invalid JSON input: {e}")


    # Validate expected keys
    required_keys = ["age", "gender", "pregnancies", "bmi", "family_history", "high_bp", "activity", "sugar_intake"]
    for key in required_keys:
        if key not in input_data:
            raise ValueError(f"Missing required input: {key}")

    # Validate input types before conversion
    try:
        age = int(input_data["age"])
        gender = int(input_data["gender"])
        pregnancies = int(input_data["pregnancies"]) if input_data["pregnancies"] is not None else 0
        bmi = float(input_data["bmi"])
        family_history = int(input_data["family_history"])
        high_bp = int(input_data["high_bp"])

        # Ensure activity and sugar intake are integers
        if not isinstance(input_data["activity"], int) or not isinstance(input_data["sugar_intake"], int):
            raise ValueError("Activity and sugar intake should be integers.")

        activity = int(input_data["activity"])
        sugar_intake = int(input_data["sugar_intake"])

    except (ValueError, TypeError) as e:
        raise ValueError(f"Invalid input type: {e}")

    # Convert validated data to NumPy array
    features = np.array([age, gender, pregnancies, bmi, family_history, high_bp, activity, sugar_intake]).reshape(1, -1)


    # Convert to DMatrix
    feature_names = ["age", "gender", "pregnancies", "bmi", "family_history", "high_bp", "activity", "sugar_intake"]
    d_matrix = xgb.DMatrix(features, feature_names=feature_names)


    # Predict
    prediction_array = model.predict(d_matrix)

    if prediction_array.size == 0:
        raise ValueError("Model returned an empty prediction array.")

    prediction = int(np.argmax(prediction_array))


    # AI-based health tips (Tip kesihatan berasaskan AI)
    health_tips = {  
        0: {
            "en": "This week, take charge of your health by adding a new form of exercise to your routine. Whether it’s a 10-minute walk after meals or a morning stretch, 'The journey of a thousand miles begins with a single step.' You have the power to make small changes that will lead to lasting health improvements. Every effort you make today brings you closer to your strongest self.",
            "ms": "Minggu ini, ambil alih kesihatan anda dengan menambah bentuk senaman baru dalam rutin anda. Sama ada berjalan 10 minit selepas makan atau regangan pagi, 'Perjalanan seribu batu bermula dengan satu langkah.' Anda mempunyai kuasa untuk membuat perubahan kecil yang akan membawa kepada penambahbaikan kesihatan yang kekal. Setiap usaha yang anda lakukan hari ini membawa anda lebih dekat kepada diri anda yang paling kuat."
        },

        1: {
            "en": "You are in control of your health, and every movement you make matters. Take a moment to add something new to your daily routine, like a walk after lunch or a quick yoga session. 'Success is the sum of small efforts, repeated day in and day out.' It's not about perfection, but it's about progress. You have the strength to make each day a step toward greater well-being.",
            "ms": "Anda mengawal kesihatan anda, dan setiap pergerakan yang anda lakukan penting. Luangkan masa untuk menambah sesuatu yang baru dalam rutin harian anda, seperti berjalan selepas makan tengahari atau sesi yoga cepat. 'Kejayaan adalah jumlah usaha kecil, diulang setiap hari.' Ia bukan tentang kesempurnaan, tetapi tentang kemajuan. Anda mempunyai kekuatan untuk menjadikan setiap hari satu langkah ke arah kesejahteraan yang lebih baik."
        },

        2: {
            "en": "Take ownership of your health today. Even 20 minutes of activity can boost your energy and set the tone for a healthier future. 'You don’t have to be great to start, but you have to start to be great.' Consistency is the key to lasting change. Every choice you make, no matter how small, empowers you to feel better, move more freely, and build a life full of vitality. Keep showing up, and you’ll see the transformation unfold.",
            "ms": "Ambil alih kesihatan anda hari ini. Bahkan 20 minit aktiviti boleh meningkatkan tenaga anda dan menetapkan nada untuk masa depan yang lebih sihat. 'Anda tidak perlu hebat untuk bermula, tetapi anda perlu bermula untuk menjadi hebat.' Konsistensi adalah kunci kepada perubahan yang kekal. Setiap pilihan yang anda buat, tidak kira betapa kecilnya, memberi kuasa kepada anda untuk berasa lebih baik, bergerak dengan lebih bebas, dan membina kehidupan yang penuh dengan vitaliti. Teruskan berusaha, dan anda akan melihat transformasi berlaku."
        }
    }

    ai_tips = health_tips.get(prediction)

    # Output JSON response
    output = {"prediction": prediction, "ai_tips": ai_tips}
    print(json.dumps(output))
    sys.stdout.flush()

except Exception as e:
    error_message = json.dumps({"error": str(e)})
    print(error_message)
    sys.stdout.flush()
    sys.exit(1)  # Ensure proper exit in case of an error