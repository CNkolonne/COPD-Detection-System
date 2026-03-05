<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->staff_id) && !empty($data->password)) {
    // 1. Password එක Query එකේදී චෙක් කරන්නේ නැතුව staff_id එකෙන් විතරක් User ව සොයන්න
    $query = "SELECT * FROM assistant WHERE staff_id = :sid";
    $stmt = $conn->prepare($query);
    $stmt->execute([':sid' => $data->staff_id]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. User කෙනෙක් ඉන්නවාද සහ Database එකේ තියෙන Hash එකට User ගහපු පාස්වර්ඩ් එක ගැලපෙනවාදැයි බලන්න
    if($user && password_verify($data->password, $user['Password'])) {
        
        // Session එක පටන් ගැනීම
        $_SESSION['staff_id'] = $user['staff_id']; 

        // ආරක්ෂාව සඳහා Password එක array එකෙන් අයින් කරනවා
        unset($user['Password']); 

        echo json_encode([
            "status" => "success", 
            "message" => "Login Successful",
            "user" => $user
        ]);
    } else {
        // ID එක වැරදි නම් හෝ Password එක වැරදි නම් මේ message එක යවනවා
        echo json_encode([
            "status" => "error", 
            "message" => "Invalid Staff ID or Password"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Missing Credentials"
    ]);
}
?>