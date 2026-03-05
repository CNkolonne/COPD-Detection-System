<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

// Danata login wela inna Assistant ge ID eka gannawa
$staff_id = $_SESSION['staff_id'] ?? 'Unknown';

// Audio file eka upload kirima
if(isset($_FILES['audio_file'])) {
    $test_id = "T" . time();
    $patient_id = $_POST['patient_id'];
    
    // Test_record table ekata data damma
    $query = "INSERT INTO Test_record (Test_id, Patient_ID, staff_id, record_date, record_start_time, Status) 
              VALUES (?, ?, ?, CURDATE(), CURTIME(), 'Completed')";
    $stmt = $conn->prepare($query);
    $stmt->execute([$test_id, $patient_id, $staff_id]);

    echo json_encode(["status" => "success", "test_id" => $test_id]);
}
?>