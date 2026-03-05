<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

// Doctor login check
if (!isset($_SESSION['MC_number'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);
    exit();
}

$doctor_mc = $_SESSION['MC_number'];

// ✅ patient_id from URL
$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    echo json_encode([
        "success" => false,
        "message" => "Patient ID missing"
    ]);
    exit();
}

try {

    $sql = "SELECT DISTINCT 
                t.Test_id, 
                t.record_date, 
                t.Status,
                t.Patient_ID 
            FROM Test_record t
            INNER JOIN appointment_details a 
                ON t.Patient_ID = a.Patient_ID
            WHERE a.MC_number = ?
            AND t.Patient_ID = ?
            ORDER BY t.record_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$doctor_mc, $patient_id]);

    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "reports" => $reports
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Database Error: " . $e->getMessage()
    ]);
}
?>