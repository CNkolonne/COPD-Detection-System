<?php
header("Content-Type: application/json");
include_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // දත්ත පරීක්ෂා කිරීම
    if (!empty($data['full_name']) && !empty($data['MC_number']) && !empty($data['password'])) {
        try {
            // Password එක Hash කිරීම (Security)
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO doctor (Name, MC_number, NIC_no, Email, Place_of_work, Contact_number, Password, Appoinment_count) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([
                $data['full_name'],
                $data['MC_number'],
                $data['nic_number'],
                $data['email'],
                $data['place_of_work'],
                $data['contact_number'],
                $hashed_password,
                $data['appointment_count']
            ]);

            if ($success) {
                echo json_encode(["success" => true, "message" => "Registration successful!"]);
            } else {
                echo json_encode(["success" => false, "message" => "Registration failed."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Please fill all required fields."]);
    }
}
?>