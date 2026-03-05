<?php
session_start();
header("Content-Type: application/json");
include_once '../db_config.php';

// පේෂන්ට් ලොග් වෙලාද කියලා බලනවා
if (!isset($_SESSION['patient_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit();
}

$patient_id = $_SESSION['patient_id'];

try {
    // 1. පේෂන්ට්ගේ මූලික තොරතුරු ලබා ගැනීම
    $sql = "SELECT Patient_ID, Name, Email, Phone_number, NIC, Date_of_birth, Other_diagnosis_details 
            FROM patient WHERE Patient_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        // --- අලුතින් එකතු කළ කොටස: Stats ලබා ගැනීම ---

        // 2. අවසන් රිසල්ට් එක (Last Result) - Test_record table එකෙන්
        $last_test_stmt = $conn->prepare("SELECT Average_positive_probability FROM Test_record WHERE Patient_ID = ? ORDER BY record_date DESC LIMIT 1");
        $last_test_stmt->execute([$patient_id]);
        $last_test = $last_test_stmt->fetch(PDO::FETCH_ASSOC);
        
        $last_result = "No Result";
        if ($last_test) {
            $prob = (double)$last_test['Average_positive_probability'];
            // Probability අගය අනුව Result එක තීරණය කිරීම
            if ($prob < 0.3) $last_result = "Normal";
            else if ($prob < 0.7) $last_result = "Mild COPD";
            else $last_result = "Severe COPD";
        }

        // 3. මීළඟ ඇපොයිමන්ට් එක (Next Appointment) - appointment_details table එකෙන්
        // අද දිනට (CURDATE) පසු ඇති ආසන්නතම ඇපොයිමන්ට් එක ගනී
        $next_app_stmt = $conn->prepare("SELECT Appoinment_date FROM appointment_details WHERE Patient_ID = ? AND Appoinment_date >= CURDATE() ORDER BY Appoinment_date ASC LIMIT 1");
        $next_app_stmt->execute([$patient_id]);
        $next_app = $next_app_stmt->fetch(PDO::FETCH_ASSOC);
        $appointment_display = $next_app ? date("Y-m-d", strtotime($next_app['Appoinment_date'])) : "Not Scheduled";

        // 4. මුළු සාම්පල ගණන (Total Samples)
        $total_samples_stmt = $conn->prepare("SELECT COUNT(*) as total FROM Test_record WHERE Patient_ID = ?");
        $total_samples_stmt->execute([$patient_id]);
        $total_samples = $total_samples_stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // සියලුම දත්ත එකට එවීම
        echo json_encode([
            "success" => true, 
            "data" => $patient,
            "stats" => [
                "last_result" => $last_result,
                "next_appointment" => $appointment_display,
                "total_samples" => $total_samples
            ]
        ]);

    } else {
        echo json_encode(["success" => false, "message" => "Patient not found."]);
    }
} catch (PDOException $e) {
    // Error එක බලාගැනීමට message එක එවන්න
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>