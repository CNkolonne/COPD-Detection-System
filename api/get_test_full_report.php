<?php
header("Content-Type: application/json");
include_once '../db_config.php';

$test_id = $_GET['test_id'] ?? null;

if (!$test_id) {
    echo json_encode(["success" => false, "message" => "No test id"]);
    exit;
}

try {

    // ===== PATIENT =====
    $sql1 = "
        SELECT p.Patient_ID,
               p.Name,
               p.NIC,
               p.Date_of_birth
        FROM patient p
        JOIN Test_record t ON p.Patient_ID = t.Patient_ID
        WHERE t.Test_id = :tid
    ";

    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute([':tid' => $test_id]);
    $patient = $stmt1->fetch(PDO::FETCH_ASSOC);

    // AGE calculate
    if ($patient) {

    if (!empty($patient['Date_of_birth'])) {
        $dob = new DateTime($patient['Date_of_birth']);
        $today = new DateTime();
        $patient['Age'] = $today->diff($dob)->y;
    } else {
        $patient['Age'] = "-";
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Patient not found for this Test ID"
    ]);
    exit;
}

    // ===== CALCULATE AVERAGES =====

$avgSql = "SELECT 
              AVG(Probability_of_positive) as avg_positive,
              AVG(Probability_of_negative) as avg_negative
           FROM samples
           WHERE Test_id = ? 
           AND process_status = 'Completed'";

$stmtAvg = $conn->prepare($avgSql);
$stmtAvg->execute([$test_id]);
$averages = $stmtAvg->fetch(PDO::FETCH_ASSOC);

$avgPositive = $averages['avg_positive'] ?? 0;
$avgNegative = $averages['avg_negative'] ?? 0;

// ===== SAVE INTO Test_record TABLE =====

$updateAvg = "UPDATE Test_record
              SET Average_positive_probability = ?,
                  Average_negative_probability = ?
              WHERE Test_id = ?";

$stmtUpdate = $conn->prepare($updateAvg);
$stmtUpdate->execute([
    $avgPositive,
    $avgNegative,
    $test_id
]);

    // ===== SAMPLES =====
    $sql2 = "
        SELECT Sample_ID,
               Audio_URL,
               Preprocess_URL,
               Spectrogram_URL,
               Probability_of_positive,
               Probability_of_negative
        FROM samples
        WHERE Test_id = :tid
        ORDER BY Sample_ID ASC
    ";

    $stmt2 = $conn->prepare($sql2);
$stmt2->execute([':tid' => $test_id]);
$samples = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// 🔥 PATH FIX HERE
foreach ($samples as &$sample) {

    if (!empty($sample['Preprocess_URL'])) {
        $sample['Preprocess_URL'] = str_replace("\\", "/", $sample['Preprocess_URL']);
    }

    if (!empty($sample['Spectrogram_URL'])) {
        $sample['Spectrogram_URL'] = str_replace("\\", "/", $sample['Spectrogram_URL']);
    }

    if (!empty($sample['Audio_URL'])) {
        $sample['Audio_URL'] = str_replace("\\", "/", $sample['Audio_URL']);
    }
}

    
echo json_encode([
    "success" => true,
    "patient" => $patient,
    "samples" => $samples,
    "average_positive" => $avgPositive,
    "average_negative" => $avgNegative
]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>