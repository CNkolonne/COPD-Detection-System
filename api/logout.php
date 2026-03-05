<?php
session_start(); // Session එක ආරම්භ කරන්න

// Session variables ඔක්කොම අයින් කරන්න
$_SESSION = array();

// Session එක සම්පූර්ණයෙන්ම විනාශ කරන්න
if (session_id() != "" || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 2592000, '/');
}

session_destroy();

// නැවත Login පේජ් එකට යොමු කරන්න
header("Location: ../home_page.html");
exit;
?>