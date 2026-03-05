
<?php
session_start();
header('Content-Type: application/json');

// 1. Path එක ලබා ගැනීම
$db_path = dirname(__DIR__) . '/db_config.php';
if (file_exists($db_path)) {
    include($db_path);
} else {
    echo json_encode(['success' => false, 'message' => 'Config file missing']);
    exit;
}

// 2. PDO Connection එක හඳුනා ගැනීම
// ඔබේ db_config එකේ variable එක $conn නොවී $pdo හෝ $db නම් එය පහතින් වෙනස් කරන්න
$db = isset($conn) ? $conn : (isset($pdo) ? $pdo : (isset($db) ? $db : null));

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Database connection variable not found.']);
    exit;
}

// 3. MC Number එක ලබා ගැනීම
$doctor_id = isset($_SESSION['MC_number']) ? $_SESSION['MC_number'] : 'MC-001'; 

try {
    $today = date('Y-m-d');
    // PDO ක්‍රමයට Query එක සකස් කිරීම
    $query = "SELECT DISTINCT p.Patient_ID, p.Name, p.NIC, p.Gender, p.Phone_number, p.Other_diagnosis_details 
              FROM patient p 
              JOIN appointment_details a ON p.Patient_ID = a.Patient_ID 
              WHERE a.MC_number = :doctor_id" ;

    $stmt = $db->prepare($query);
    
    // PDO වලදී පාවිච්චි කරන්නේ bindParam මිස bind_param නොවේ
    $stmt->bindParam(':doctor_id', $doctor_id);
    
    $stmt->execute();
    
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'count' => count($patients),
        'debug_mc' => $doctor_id,
        'patients' => $patients
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'General Error: ' . $e->getMessage()]);
}
?>