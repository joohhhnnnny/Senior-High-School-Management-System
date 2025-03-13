<?php
session_start();
session_destroy();
header("Location: ../../FINAL-ADMIN/Portal-Main/main.php");
exit();
?>
