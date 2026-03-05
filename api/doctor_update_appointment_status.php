<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['appt_no']) && isset($data['status'])) {
    try {
        $sql = "UPDATE appointment_details SET COPD_Test_recommend = ? WHERE Appoinment_no = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$data['status'], $data['appt_no']]);

        echo json_encode(["success" => $result]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
?>