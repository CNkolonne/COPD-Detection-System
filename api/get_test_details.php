<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

if (!isset($_GET['test_id'])) {
    echo json_encode(["success" => false, "message" => "Test ID is required"]);
    exit();
}

$test_id = $_GET['test_id'];

try {
    // 1. Test_record සහ Patient සම්බන්ධ කර දත්ත ලබා ගැනීම
    $sql = "SELECT t.*, p.Name, p.NIC, p.Date_of_birth, p.Patient_ID 
            FROM Test_record t 
            JOIN patient p ON t.Patient_ID = p.Patient_ID 
            WHERE t.Test_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$test_id]);
    $main_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$main_data) {
        echo json_encode(["success" => false, "message" => "Record not found"]);
        exit();
    }

    // වයස ගණනය කිරීම (Date_of_birth text එකක් නිසා DateTime භාවිතා කරයි)
    $dob = new DateTime($main_data['Date_of_birth']);
    $today = new DateTime();
    $age = $today->diff($dob)->y;

    // 2. අදාළ Test_id එකට අයත් සියලුම Samples ලබා ගැනීම
    $sql_samples = "SELECT * FROM samples WHERE Test_id = ?";
    $stmt_s = $conn->prepare($sql_samples);
    $stmt_s->execute([$test_id]);
    $samples = $stmt_s->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "patient" => [
            "name" => $main_data['Name'],
            "nic" => $main_data['NIC'],
            "id" => $main_data['Patient_ID'],
            "age" => $age
        ],
        "summary" => [
            "pos_prob" => round($main_data['Average_positive_probability'] * 100, 1),
            "neg_prob" => round($main_data['Average_negative_probability'] * 100, 1),
            "status" => $main_data['Status']
        ],
        "samples" => $samples
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>