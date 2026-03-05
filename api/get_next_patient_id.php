<?php
header("Content-Type: application/json");
include_once '../db_config.php';

try {
    // Patient_ID එකේ අගයන් DESC (වැඩි සිට අඩුට) අනුපිළිවෙලට ගෙන පළමු අගය ලබා ගනී
    $sql = "SELECT Patient_ID FROM patient ORDER BY Patient_ID DESC LIMIT 1";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // ලැබෙන ID එකේ 'P' අකුර ඉවත් කර ඉතිරි අංකය ගනී (උදා: P0005 -> 5)
        $last_id_num = (int)substr($row['Patient_ID'], 1);
        $next_id_num = $last_id_num + 1;
    } else {
        // Database එක හිස් නම් පළමු අංකය 1 ලෙස ගනී
        $next_id_num = 1;
    }

    // අංකය නැවත P0000 ආකෘතියට සකසයි (උදා: 6 -> P0006)
    $next_patient_id = 'P' . str_pad($next_id_num, 4, '0', STR_PAD_LEFT);

    echo json_encode(["success" => true, "next_id" => $next_patient_id]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>