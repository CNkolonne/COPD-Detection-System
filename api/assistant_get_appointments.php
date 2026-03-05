<?php
// Debugging සඳහා errors පෙන්වන්න
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// db_config.php පරීක්ෂාව
if (!file_exists('../db_config.php')) {
    echo json_encode(["error" => "db_config.php file not found. Check your folder structure."]);
    exit;
}

include_once '../db_config.php'; 

if (!isset($conn)) {
    echo json_encode(["error" => "Database connection variable (\$conn) is not defined."]);
    exit;
}

try {
    // Column names හරියටම JavaScript එකේ row.Name වලට ගැලපෙන්න alias (AS) පාවිච්චි කර ඇත
    $sql = "SELECT 
                Patient_ID, 
                Appoinment_no, 
                Appoinment_date, 
                MC_number, 
                staff_id, 
                COPD_Test_recommend 
            FROM appointment_details 
            ORDER BY Appoinment_date DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($appointments);

} catch (PDOException $e) {
    echo json_encode(["error" => "SQL Error: " . $e->getMessage()]);
}
?>