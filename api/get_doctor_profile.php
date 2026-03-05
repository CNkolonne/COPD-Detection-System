<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

if (!isset($_SESSION['MC_number'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit();
}

$MC_number = $_SESSION['MC_number'];

try {
    // 1. Get Doctor's Basic Profile
    $sql = "SELECT Name, MC_number, NIC_no, Email, Place_of_work, Contact_number FROM doctor WHERE MC_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$MC_number]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doctor) {
        // 2. Total Unique Patients (Ever seen by this doctor)
        $sql_patients = "SELECT COUNT(DISTINCT Patient_ID) as total_patients FROM appointment_details WHERE MC_number = ?";
        $stmt_p = $conn->prepare($sql_patients);
        $stmt_p->execute([$MC_number]);
        $p_data = $stmt_p->fetch(PDO::FETCH_ASSOC);

        // 3. TODAY'S Appointments Count (Filtered by the current date)
        // Note: Using CURDATE() ensures we only get records for today
        $sql_today = "SELECT COUNT(*) as today_count FROM appointment_details 
                      WHERE MC_number = ? AND Appoinment_date = CURDATE()";
        $stmt_t = $conn->prepare($sql_today);
        $stmt_t->execute([$MC_number]);
        $t_data = $stmt_t->fetch(PDO::FETCH_ASSOC);

        // 4. Recent Analyses (Completed tests in the last 24 hours)
        $sql_recent = "SELECT COUNT(*) as recent_count FROM appointment_details 
                       WHERE MC_number = ? AND Appoinment_date >= NOW() - INTERVAL 1 DAY 
                       AND COPD_Test_recommend = 'Yes'"; 
        $stmt_r = $conn->prepare($sql_recent);
        $stmt_r->execute([$MC_number]);
        $r_data = $stmt_r->fetch(PDO::FETCH_ASSOC);

        // Mapping data to the keys expected by your JavaScript
        $doctor['total_patients_count'] = $p_data['total_patients'];
        $doctor['today_appointments_count'] = $t_data['today_count'];
        $doctor['recent_analyses_count'] = $r_data['recent_count'];

        echo json_encode(["success" => true, "data" => $doctor]);
    } else {
        echo json_encode(["success" => false, "message" => "Doctor record not found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
}
?>