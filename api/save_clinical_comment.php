<?php
header("Content-Type: application/json");
include_once '../db_config.php';

$data = json_decode(file_get_contents("php://input"), true);

$test_id = $data['test_id'] ?? null;
$comment = $data['comment'] ?? null;

if (!$test_id || !$comment) {
    echo json_encode([
        "success" => false,
        "message" => "Missing test_id or comment"
    ]);
    exit;
}

try {

    $sql = "UPDATE Test_record 
            SET clinical_comment = :comment 
            WHERE Test_id = :test_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':comment' => $comment,
        ':test_id' => $test_id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Comment saved successfully"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>