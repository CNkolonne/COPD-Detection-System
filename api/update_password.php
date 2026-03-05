<?php
// api/update_password.php
require_once '../db_config.php';
session_start();

if (isset($_SESSION['reset_user_id'])) {
    $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT); // Password ආරක්ෂාව සඳහා Hash කිරීම
    $user_id = $_SESSION['reset_user_id'];
    $table = $_SESSION['reset_table'];
    $id_field = $_SESSION['reset_id_field'];

    $sql = "UPDATE $table SET Password = ? WHERE $id_field = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$new_pass, $user_id])) {
        session_destroy(); // Reset කිරීමෙන් පසු Session එක අයින් කරන්න
        
        // අදාළ Login පිටුවට යැවීම
        $login_page = ($table == "assistant") ? "assistant_login.html" : (($table == "doctor") ? "doctor_login.html" : "patient_login.html");
        
        echo "<script>alert('Password updated successfully!'); window.location.href='../$login_page';</script>";
    } else {
        echo "Error updating password.";
    }
} else {
    header("Location: ../forgot_password.html");
}
?>