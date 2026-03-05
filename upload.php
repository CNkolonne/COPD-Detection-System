<?php
header('Content-Type: application/json');
include 'db_config.php';

date_default_timezone_set("Asia/Colombo");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio'])) {
    
    $patient_id = $_POST['patient_id'] ?? 'Unknown';
    $test_id = $_POST['test_id'] ?? 'T-' . time();
    $sample_no = isset($_POST['sample_no']) ? (int)$_POST['sample_no'] : 0;
    
    $staff_id = 'S-001'; 

    $dir = "uploads/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $file_name = $patient_id . "_" . $test_id . "_S" . $sample_no . "_" . time() . ".webm";
    $upload_path = $dir . $file_name;

    if (move_uploaded_file($_FILES['audio']['tmp_name'], $upload_path)) {
        
        // ===============================
        // 🔥 Flask Model API Call
        // ===============================
        $prediction_value = null;

        $cfile = new CURLFile(realpath($upload_path));
        $data = array('file' => $cfile);

        $ch = curl_init("http://127.0.0.1:5000/predict");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $prediction_result = json_decode($response, true);
            $prediction_value = $prediction_result['prediction'][0][0] ?? null;
        }

        // --- Sample ID ---
        $sample_id = $patient_id . "-S" . $sample_no;
        
        $current_date = date("Y-m-d");
        $current_time = date("H:i:s");

        try {
            // 1. samples table
            $sql1 = "INSERT INTO samples (Test_id, Sample_ID, Audio_URL, Sample_date, Sample_time, Patient_ID) 
                     VALUES (:test_id, :sample_id, :audio_url, :sample_date, :sample_time, :patient_id)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->execute([
                ':test_id' => $test_id,
                ':sample_id' => $sample_id,
                ':audio_url' => $upload_path,
                ':sample_date' => $current_date,
                ':sample_time' => $current_time,
                ':patient_id' => $patient_id
            ]);

            // 2. Test_record table
            if ($sample_no === 1) {
                $sql2 = "INSERT INTO Test_record (Test_id, Patient_ID, record_date, `record_start time`, staff_id, Status) 
                         VALUES (:test_id, :patient_id, :r_date, :start, :staff, :status)";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([
                    ':test_id' => $test_id,
                    ':patient_id' => $patient_id,
                    ':r_date' => $current_date,
                    ':start' => $current_time,
                    ':staff' => $staff_id,
                    ':status' => 'Processing'
                ]);
            } 
            
            if ($sample_no === 3) {
                $sql3 = "UPDATE Test_record 
                         SET `record_end time` = :end, Status = 'Completed' 
                         WHERE Test_id = :tid";
                $stmt3 = $conn->prepare($sql3);
                $stmt3->execute([
                    ':end' => $current_time,
                    ':tid' => $test_id
                ]);
            }

            echo json_encode([
                "status" => "success",
                "message" => "Sample $sample_id saved successfully",
                "prediction" => $prediction_value
            ]);

        } catch (PDOException $e) {
            echo json_encode([
                "status" => "error",
                "message" => "SQL Error: " . $e->getMessage()
            ]);
        }

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Upload failed"
        ]);
    }
}
?>