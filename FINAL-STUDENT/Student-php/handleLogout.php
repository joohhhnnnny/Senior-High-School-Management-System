<?php
session_start();
session_destroy(); // Destroy all session data
session_write_close(); // Write session data and close session
setcookie(session_name(), '', time() - 3600, '/'); // Clear session cookie
header("Location: ../../FINAL-ADMIN/Portal-Main/main.php"); // Redirect to login page
exit();