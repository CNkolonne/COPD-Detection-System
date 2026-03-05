<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

if (!isset($_SESSION['patient_id'])) {
    echo json_encode(["success" => false, "message" => "Session expired."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['mc_number']) && !empty($data['app_date'])) {
    try {
        $patient_id = $_SESSION['patient_id'];
        $mc_number = $data['mc_number'];
        $staff_id = $data['staff_id'] ?? null;
        $app_date = $data['app_date'];

        // --- පියවර 1: වෛද්‍යවරයාගේ උපරිම සීමාව සහ දැනට ඇති appointments ගණන පරීක්ෂා කිරීම ---
        
        // වෛද්‍යවරයාට දිනකට ගත හැකි උපරිම ගණන (appointment_count) ලබා ගැනීම
        $limit_sql = "SELECT Appoinment_count FROM doctor WHERE MC_number = ?";
        $limit_stmt = $conn->prepare($limit_sql);
        $limit_stmt->execute([$mc_number]);
        $doctor_info = $limit_stmt->fetch(PDO::FETCH_ASSOC);
        
        $max_allowed = $doctor_info ? (int)$doctor_info['Appoinment_count'] : 0;

        // තෝරාගත් දිනයට අදාළව මෙම වෛද්‍යවරයාට දැනට වෙන් කර ඇති appointments ගණන බැලීම
        $count_sql = "SELECT COUNT(*) as current_total FROM appointment_details WHERE MC_number = ? AND Appoinment_date = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->execute([$mc_number, $app_date]);
        $current_total = (int)$count_stmt->fetch(PDO::FETCH_ASSOC)['current_total'];

        // --- පියවර 2: සීමාව ඉක්මවා ඇත්නම් පණිවිඩයක් යැවීම ---
        if ($current_total >= $max_allowed) {
            echo json_encode([
                "success" => false, 
                "message" => "This doctor's appointment limit for the selected date has been reached. Please select another date or another doctor."
            ]);
            exit();
        }

        // --- පියවර 3: සීමාව ඉක්මවා නැතිනම් සාමාන්‍ය පරිදි Insert කිරීම ---
        $sql = "INSERT INTO appointment_details (Patient_ID, Appoinment_date, staff_id, MC_number) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$patient_id, $app_date, $staff_id, $mc_number]);

        if ($result) {
            echo json_encode(["success" => true, "message" => "Appointment booked successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Booking failed."]);
        }
        
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Please select a doctor and date."]);
}
?>