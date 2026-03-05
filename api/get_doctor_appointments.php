<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

if (!isset($_SESSION['MC_number'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

$MC_number = $_SESSION['MC_number'];

try {
    // UPDATED SQL: Added 'AND a.Appoinment_date = CURDATE()' to filter for today only
    $sql = "SELECT a.*, p.Name as PatientName 
            FROM appointment_details a 
            JOIN patient p ON a.Patient_ID = p.Patient_ID 
            WHERE a.MC_number = ? 
            AND a.Appoinment_date = CURDATE() 
            ORDER BY a.Appoinment_date ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$MC_number]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats Calculation for Today
    $total = count($appointments);
    $confirmed = 0;
    $pending = 0;

    foreach ($appointments as $app) {
        // Checking if test is recommended
        if ($app['COPD_Test_recommend'] === 'Yes') {
            $confirmed++;
        } else {
            $pending++;
        }
    }

    echo json_encode([
        "success" => true,
        "today_date" => date('Y-m-d'), // Added for frontend display
        "appointments" => $appointments,
        "stats" => [
            "total" => $total,
            "confirmed" => $confirmed,
            "pending" => $pending
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
}
?>