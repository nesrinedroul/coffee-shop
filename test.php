<?php
// Password to be hashed
$admin_password = '13102004';  // Replace with the admin password you want
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Output the hashed password
echo $hashed_password;
?>
