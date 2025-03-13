<?php
require_once '../Teacher-php/session_check.php';
checkTeacherSession();
?>

<nav class="sidebar-nav">
    <ul>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'teacher_dashboard.php' ? 'active' : ''; ?>">
            <a href="/CST5-PROJECT/FINAL-TEACHER/Teacher-html/teacher_dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'grades_management.php' ? 'active' : ''; ?>">
            <a href="/CST5-PROJECT/FINAL-TEACHER/Teacher-html/grades_management.php">
                <i class="fas fa-book-reader"></i>
                <span>Grades Management</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'attendance_management.php' ? 'active' : ''; ?>">
            <a href="/CST5-PROJECT/FINAL-TEACHER/Teacher-html/attendance_management.php">
                <i class="fas fa-user-check"></i>
                <span>Attendance Management</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'teacher_schedule.php' ? 'active' : ''; ?>">
            <a href="/CST5-PROJECT/FINAL-TEACHER/Teacher-html/teacher_schedule.php">
                <i class="fas fa-calendar-alt"></i>
                <span>Schedule</span>
            </a>
        </li>

        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'teacher_profile.php' ? 'active' : ''; ?>">
            <a href="/CST5-PROJECT/FINAL-TEACHER/Teacher-html/teacher_profile.php">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="/CST5-PROJECT/FINAL-TEACHER/Teacher-php/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>
