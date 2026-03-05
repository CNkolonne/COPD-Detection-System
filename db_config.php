<?php
$host = "mydatabase.cjy6mwwai4oa.eu-north-1.rds.amazonaws.com";
$db_name = "copd_db";
$username = "copd_admin"; // Oyage DB username eka
$password = "vmNBVmxmab7LwdZ";     // Oyage DB password eka

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>