<?php
session_start();
session_unset();  // Remove all session variables
session_destroy();  // Destroy the session

// Redirect to login page after logout
header("Location: login.php");
exit();
?>
