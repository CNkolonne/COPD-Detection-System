<?php
// කිසිම හිස් පේළියක් මෙතනින් උඩ තියෙන්න දෙන්න එපා
header("Content-Type: application/json");
include_once '../db_config.php';

// Error reporting off කරමු junk output එන එක නවත්වන්න
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['patientid']) && !empty($data['fullname']) && !empty($data['password'])) {
        try {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO patient (
                        Patient_ID, Name, Gender, Email, Password, 
                        Date_of_birth, NIC, Phone_number, Other_diagnosis_details
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            
            $result = $stmt->execute([
                $data['patientid'],
                $data['fullname'],
                $data['gender'],
                $data['email'],
                $hashed_password,
                $data['dob'],
                $data['nic'],
                $data['phone'],
                $data['diagnosis']
            ]);

            if ($result) {
                echo json_encode(["success" => true, "message" => "Patient registered successfully!"]);
                exit(); // මෙතනින් script එක නවත්වනවා
            } else {
                echo json_encode(["success" => false, "message" => "Registration failed."]);
                exit();
            }

        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "Please fill all required fields."]);
        exit();
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}
