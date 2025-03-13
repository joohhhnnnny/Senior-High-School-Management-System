<?php
session_start();

// Check if teacher is logged in with all required session variables
if (!isset($_SESSION['teacher_logged_in']) || 
    $_SESSION['teacher_logged_in'] !== true || 
    !isset($_SESSION['professor_name']) || 
    !isset($_SESSION['professor_email'])) {
    header("Location: ../../FINAL-ADMIN/Portal-Main/main.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teaching Schedule - ISCP</title>
    <link rel="stylesheet" href="../Teacher-css/teacher_dashboard.css">
    <link rel="stylesheet" href="../Teacher-css/teacher_schedule.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../Teacher-IMAGES/iscp-logo.webp" alt="ISCP Logo" class="logo">
            <div class="teacher-info">
                <h2><?php echo htmlspecialchars($_SESSION['professor_name']); ?></h2>
                <p class="teacher-email"><?php echo htmlspecialchars($_SESSION['professor_email']); ?></p>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>

                <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'teacher_profile.php' ? 'active' : ''; ?>">
                    <a href="teacher_profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>

                <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'teacher_dashboard.php' ? 'active' : ''; ?>">
                    <a href="teacher_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'grades_management.php' ? 'active' : ''; ?>">
                    <a href="grades_management.php">
                        <i class="fas fa-book-reader"></i>
                        <span>Grades Management</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'attendance_management.php' ? 'active' : ''; ?>">
                    <a href="attendance_management.php">
                        <i class="fas fa-user-check"></i>
                        <span>Attendance Management</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'teacher_schedule.php' ? 'active' : ''; ?>">
                    <a href="teacher_schedule.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Schedule</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../Teacher-php/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-container">
            <div class="schedule-container">
                <div class="section-header">
                    <div class="header-content">
                        <h2>Teaching Schedule</h2>
                        <div class="grade-level-display">
                            <span>Grade Level:</span>
                            <select id="gradeLevel" class="grade-select">
                                <!-- Will be populated by JavaScript -->
                            </select>
                        </div>
                    </div>
                </div>

                <div class="schedule-grid">
                    <div class="time-column">
                        <div class="column-header">Time</div>
                        <div class="time-slot">7:00 AM - 9:00 AM</div>
                        <div class="time-slot">9:00 AM - 11:00 AM</div>
                        <div class="time-slot">11:00 AM - 1:00 PM</div>
                        <div class="time-slot">1:00 PM - 3:00 PM</div>
                        <div class="time-slot">3:00 PM - 5:00 PM</div>
                        <div class="time-slot">5:00 PM - 7:00 PM</div>
                    </div>

                    <div class="schedule-content">
                        <div class="days-row">
                            <div class="day">Monday</div>
                            <div class="day">Tuesday</div>
                            <div class="day">Wednesday</div>
                            <div class="day">Thursday</div>
                            <div class="day">Friday</div>
                        </div>

                        <div class="classes-grid">
                            <!-- Classes will be dynamically inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../Teacher-js/teacher_schedule.js"></script>
</body>
</html>
