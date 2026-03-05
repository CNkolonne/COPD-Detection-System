<?php
header("Content-Type: application/json");
include_once '../db_config.php';

try {
    $response = [];
    $today = date('Y-m-d');

    // 1. Total Patients Count
    $q1 = $conn->query("SELECT COUNT(*) as total FROM patients");
    $response['total_patients'] = $q1->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Today's Appointments Count
    // මෙහි 'appointment_date' යනු ඔබේ table එකේ column නම විය යුතුයි
    $q2 = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE appointment_date = :today");
    $q2->execute(['today' => $today]);
    $response['today_appointments'] = $q2->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Pending Count (Register වෙලා ඉන්න නමුත් අදට appointment එකක් නැති අය)
    // සටහන: මෙහි logic එක ඔබේ අවශ්‍යතාවය අනුව 'pending' තත්ත්වය මත වෙනස් විය හැක
    $q3 = $conn->prepare("SELECT COUNT(*) as total FROM patients 
                          WHERE Patient_ID NOT IN (SELECT Patient_ID FROM appointments WHERE appointment_date = :today)");
    $q3->execute(['today' => $today]);
    $response['pending_patients'] = $q3->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode($response);

} catch(Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>