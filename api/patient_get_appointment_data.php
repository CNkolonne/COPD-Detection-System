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
    // 1. Doctor table එකෙන් දත්ත (Name - Capital N)
    $doc_stmt = $conn->query("SELECT Name, MC_number FROM doctor");
    $doctors = $doc_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Assistant table එකෙන් දත්ත (name - Simple n)
    // ඔයාගේ Schema එකේ ටේබල් එක 'assistant' මිස 'staff' නෙවෙයි
    $asst_stmt = $conn->query("SELECT name, staff_id FROM assistant"); 
    $assistants = $asst_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Appointment ලැයිස්තුව
    $app_sql = "SELECT a.*, d.Name as DoctorName 
                FROM appointment_details a 
                LEFT JOIN doctor d ON a.MC_number = d.MC_number 
                WHERE a.Patient_ID = ? 
                ORDER BY a.Appoinment_date DESC";
    $app_stmt = $conn->prepare($app_sql);
    $app_stmt->execute([$patient_id]);
    $appointments = $app_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "doctors" => $doctors,
        "assistants" => $assistants,
        "appointments" => $appointments
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
}
?>