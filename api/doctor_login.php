<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['MC_number']) && !empty($data['password'])) {
        try {
            // MC Number එකෙන් දොස්තරගේ දත්ත ලබා ගැනීම
            $sql = "SELECT MC_number, Password, Name FROM doctor WHERE MC_number = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$data['MC_number']]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

            // දොස්තර කෙනෙක් ඉන්නවාද සහ Password එක නිවැරදිද බලනවා
            if ($doctor && password_verify($data['password'], $doctor['Password'])) {
                // Session එකේ MC Number එක සහ නම සේව් කරනවා
                $_SESSION['MC_number'] = $doctor['MC_number'];
                $_SESSION['doctor_name'] = $doctor['Name'];

                echo json_encode(["success" => true, "message" => "Login successful!"]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid MC Number or Password."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Please enter both MC Number and Password."]);
    }
}
?>