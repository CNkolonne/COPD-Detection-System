<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php'; // db_config එකට යන නිවැරදි මාර්ගය පරීක්ෂා කරන්න

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['test_id']) || !isset($data['prescription'])) {
    echo json_encode(["success" => false, "message" => "Required data missing"]);
    exit();
}

$test_id = $data['test_id'];
$prescription = $data['prescription'];

try {
    // 1. Test_id එකට අදාළ Patient_ID එක ලබා ගැනීම
    $sql_get_patient = "SELECT Patient_ID FROM Test_record WHERE Test_id = ?";
    $stmt1 = $conn->prepare($sql_get_patient);
    $stmt1->execute([$test_id]);
    $result = $stmt1->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $patient_id = $result['Patient_ID'];

        // 2. අදාළ රෝගියාගේ අවසන් Appointment අංකය සොයා ගැනීම
        $sql_last_app = "SELECT Appoinment_no FROM appointment_details 
                         WHERE Patient_ID = ? 
                         ORDER BY Appoinment_date DESC LIMIT 1";
        $stmt_app = $conn->prepare($sql_last_app);
        $stmt_app->execute([$patient_id]);
        $app_data = $stmt_app->fetch(PDO::FETCH_ASSOC);

        if ($app_data) {
            $app_no = $app_data['Appoinment_no'];

            // 3. Doctor_prescription එක Update කිරීම
            $sql_update = "UPDATE appointment_details 
                           SET Doctor_prescription = ? 
                           WHERE Appoinment_no = ?";
            
            $stmt2 = $conn->prepare($sql_update);
            $success = $stmt2->execute([$prescription, $app_no]);

            echo json_encode(["success" => true, "message" => "Saved successfully to Appointment No: " . $app_no]);
        } else {
            echo json_encode(["success" => false, "message" => "No appointment found for this patient"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid Test ID"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>