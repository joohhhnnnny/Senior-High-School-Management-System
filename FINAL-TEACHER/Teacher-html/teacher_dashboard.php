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
    <title>Teacher Dashboard - ISCP</title>
    <link rel="stylesheet" href="../Teacher-css/teacher_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <div class="dashboard-header">
                <h2>Teacher Dashboard</h2>
                <div class="date-filter">
                    <select id="semesterSelect">
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                    </select>
                    <select id="yearSelect">
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
            </div>

            <div class="quick-stats">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <div class="stat-info">
                        <h3>Total Subjects</h3>
                        <p id="totalSubjects">0</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h3>Total Students</h3>
                        <p id="totalStudents">0</p>
                    </div>
                </div>
            </div>

            <div class="schedule-overview">
                <h3>Today's Schedule</h3>
                <div class="schedule-list" id="todaySchedule">
                    <!-- Schedule items will be populated by JavaScript -->
                </div>
            </div>

            <style>
                .status {
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 0.8em;
                    font-weight: bold;
                }
                .status.upcoming {
                    background-color: #e3f2fd;
                    color: #1976d2;
                }
                .status.ongoing {
                    background-color: #e8f5e9;
                    color: #2e7d32;
                }
                .status.done {
                    background-color: #eeeeee;
                    color: #757575;
                }
            </style>
        </div>
    </div>

    <script src="../Teacher-js/teacher_dashboard.js"></script>
</body>
</html>
