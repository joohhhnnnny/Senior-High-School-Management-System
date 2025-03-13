<?php
session_start();

function checkTeacherSession() {
    if (!isset($_SESSION['teacher_logged_in']) || 
        $_SESSION['teacher_logged_in'] !== true || 
        !isset($_SESSION['professor_id']) || 
        !isset($_SESSION['professor_name']) || 
        !isset($_SESSION['professor_email']) || 
        $_SESSION['user_type'] !== 'teacher') {
        
        // Fix the redirect path
        header("Location: /CST5-PROJECT/FINAL-ADMIN/Portal-Main/main.php");
        exit();
    }
}
?>
