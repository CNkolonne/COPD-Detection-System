<?php
header("Content-Type: application/json");
include_once '../db_config.php';

try {
    $stmt = $conn->query("SELECT Patient_ID, Name, NIC, Gender, Phone_number FROM patient");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($patients);
} catch(Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>