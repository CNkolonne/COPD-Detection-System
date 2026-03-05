<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

// JSON දත්ත ලබා ගැනීම
$data = json_decode(file_get_contents("php://input"), true);

// 1. පරීක්ෂා කරන්නේ 'email' එකද කියලා බලන්න (කලින් තිබුණේ patientid)
if (!empty($data['email']) && !empty($data['password'])) {
    try {
        // 2. Query එක Patient_ID වෙනුවට Email එකට වෙනස් කළා
        $sql = "SELECT * FROM patient WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$data['email']]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($patient) {
            // Password එක verify කිරීම
            if (password_verify($data['password'], $patient['Password'])) {
                
                // Login එක ස්ථිර කිරීමට Session variables සැකසීම
                $_SESSION['patient_logged_in'] = true;
                $_SESSION['patient_id'] = $patient['Patient_ID'];
                $_SESSION['patient_name'] = $patient['Name'];

                echo json_encode(["success" => true, "message" => "Login successful!"]);
            } else {
                // 3. Error message එක වඩාත් පොදු එකක් ලෙස වෙනස් කළා
                echo json_encode(["success" => false, "message" => "Invalid Email or Password."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Invalid Email or Password."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Please enter Email and Password."]);
}
?>