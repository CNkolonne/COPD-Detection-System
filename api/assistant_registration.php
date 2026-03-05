<?php
// වැදගත්: Error reporting off කරන්න (JSON එකට බාධා නොවන ලෙස)
error_reporting(0);
header("Content-Type: application/json");

// Database connection එක ඇතුළත් කරන්න (ඔබේ db_config.php පාවිච්චි කරන්න)
include_once '../db_config.php';

// 1. ලැබෙන දත්ත ලබා ගැනීම
$inputData = file_get_contents("php://input");
$data = json_decode($inputData, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // දත්ත තිබේදැයි පරීක්ෂා කිරීම
    if (
        empty($data['staff_id']) || empty($data['name']) || 
        empty($data['email']) || empty($data['password']) || 
        empty($data['nic'])
    ) {
        echo json_encode(["status" => "error", "message" => "Please fill all required fields."]);
        exit;
    }

    // 2. දත්ත පිරිසිදු කිරීම (Sanitizing)
    $staff_id = htmlspecialchars(strip_tags($data['staff_id']));
    $name = htmlspecialchars(strip_tags($data['name']));
    $nic = htmlspecialchars(strip_tags($data['nic']));
    $place_of_work = htmlspecialchars(strip_tags($data['place_of_work']));
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $phone_number = htmlspecialchars(strip_tags($data['phone_number']));
    
    // 3. Password එක ආරක්ෂිතව Hash කිරීම (BCRYPT)
    $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

    try {
        // 4. Staff ID හෝ Email එක දැනටමත් තිබේදැයි පරීක්ෂා කිරීම
        $check = $conn->prepare("SELECT staff_id FROM assistant WHERE staff_id = ? OR email = ?");
        $check->execute([$staff_id, $email]);
        
        if ($check->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "Staff ID or Email already exists."]);
            exit;
        }

        // 5. Database එකට ඇතුළත් කිරීම
        $sql = "INSERT INTO assistant (staff_id, name, nic, place_of_work, email, phone_number, password) 
                VALUES (:sid, :name, :nic, :pow, :email, :phone, :pass)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ':sid' => $staff_id,
            ':name' => $name,
            ':nic' => $nic,
            ':pow' => $place_of_work,
            ':email' => $email,
            ':phone' => $phone_number,
            ':pass' => $hashed_password
        ]);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Assistant registered successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to register assistant."]);
        }

    } catch (PDOException $e) {
        // Database Errors පෙන්වීම
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>