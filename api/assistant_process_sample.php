<?php
header("Content-Type: application/json");
include_once '../db_config.php';

$data = json_decode(file_get_contents("php://input"), true);

$sample_id = $data['sample_id'] ?? null;

if (!$sample_id) {
    echo json_encode(["success" => false, "message" => "No sample ID"]);
    exit;
}

try {

    // =========================
    // Get audio path from DB
    // =========================
    $sql = "SELECT Audio_URL FROM samples WHERE Sample_ID = :sid";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':sid' => $sample_id]);
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sample) {
        echo json_encode(["success" => false, "message" => "Sample not found"]);
        exit;
    }

    $audio_path = "../" . $sample['Audio_URL'];

    if (!file_exists($audio_path)) {
        echo json_encode(["success" => false, "message" => "Audio file not found"]);
        exit;
    }

    // =========================
    // Send file to Flask model
    // =========================
    $cfile = new CURLFile(realpath($audio_path));

    $postData = [
        'file' => $cfile,
        'sample_id' => $sample_id
    ];

    $ch = curl_init("http://127.0.0.1:5000/predict");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        echo json_encode(["success" => false, "message" => "Model connection failed"]);
        exit;
    }

    $result = json_decode($response, true);

    // =========================
    // Extract values
    // =========================
    $probability = $result['prediction'][0][0] ?? 0;
    $positive = $probability;
    $negative = 1 - $probability;

    $preprocess = $result['preprocess_url'] ?? null;
    $spectrogram = $result['spectrogram_url'] ?? null;

    // =========================
    // Save result to DB
    // =========================
    $update = "UPDATE samples 
           SET Probability_of_positive = :pos,
               Probability_of_negative = :neg,
               Preprocess_URL = :pre,
               Spectrogram_URL = :spec,
               process_status = 'Completed'
           WHERE Sample_ID = :sid";

$stmt2 = $conn->prepare($update);
$stmt2->execute([
    ':pos' => $positive,
    ':neg' => $negative,
    ':pre' => $preprocess,
    ':spec' => $spectrogram,
    ':sid' => $sample_id
]);

    echo json_encode([
        "success" => true,
        "message" => "Processing completed",
        "probability" => $positive,
        "preprocess_url" => $preprocess,
        "spectrogram_url" => $spectrogram
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>