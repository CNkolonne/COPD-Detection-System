<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

if (!isset($_SESSION['patient_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit();
}

$patient_id = $_SESSION['patient_id'];

try {
    // 1. රෝගියාගේ නම ලබා ගැනීම (Patient table එකෙන්)
    $p_stmt = $conn->prepare("SELECT Name FROM patient WHERE Patient_ID = ?");
    $p_stmt->execute([$patient_id]);
    $patient_data = $p_stmt->fetch(PDO::FETCH_ASSOC);

    // 2. පරීක්ෂණ වාර්තා ලබා ගැනීම (Test_record table එකෙන්)
    // මෙහිදී Average_positive_probability අගයද ලබා ගනී
    $t_stmt = $conn->prepare("SELECT Test_id, record_date, Average_positive_probability, Status 
                              FROM Test_record 
                              WHERE Patient_ID = ? 
                              ORDER BY record_date DESC");
    $t_stmt->execute([$patient_id]);
    $history = $t_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "patient_name" => $patient_data['Name'] ?? 'Unknown Patient',
        "patient_id" => $patient_id,
        "history" => $history
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
}
?>