<?php
// api/forgot_password_process.php
require_once '../db_config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = $_POST['user_type'];
    $email = $_POST['email'];
    
    // User type එක අනුව Table එක තෝරා ගැනීම
    $table = "";
    $id_field = "";
    if ($user_type == "assistant") { $table = "assistant"; $id_field = "staff_id"; }
    elseif ($user_type == "patient") { $table = "patient"; $id_field = "Patient_ID"; }
    elseif ($user_type == "doctor") { $table = "doctor"; $id_field = "MC_number"; }

    $stmt = $conn->prepare("SELECT $id_field FROM $table WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['reset_user_id'] = $user[$id_field];
        $_SESSION['reset_table'] = $table;
        $_SESSION['reset_id_field'] = $id_field;
        
        echo "<script>alert('User verified! Proceed to change password.'); window.location.href='../reset_password.html';</script>";
    } else {
        echo "<script>alert('Email not found!'); window.history.back();</script>";
    }
}
?>