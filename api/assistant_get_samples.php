<?php
header("Content-Type: application/json");
include_once '../db_config.php';

try {
    // Database eke samples table eken data okkoma gannawa
$query = "SELECT Test_id, 
                 Sample_ID, 
                 Sample_date, 
                 Sample_time, 
                 Patient_ID, 
                 Probability_of_positive, 
                 Probability_of_negative,
                 process_status
          FROM samples 
          ORDER BY Sample_date DESC, Sample_time DESC";    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($samples);
} catch(Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>