<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /CST5-PROJECT/FINAL-ADMIN/Portal-Main/main.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Admin-CSS/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../Admin-IMAGES/iscp-logo.webp" alt="ISCP Logo" class="logo">
            <h2>ISCP Admin</h2>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="menu-item active">
                    <a href="dashboard.php">  <!-- Changed from dashboard.php -->
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="student_management.php">
                        <i class="fas fa-user-graduate"></i>
                        <span>Student Management</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="active_enrollments.php">
                        <i class="fas fa-file-signature"></i>
                        <span>Student Enrollments</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="teacher_management.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Teacher Management</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="pending_applications.php">
                        <i class="fas fa-user-plus"></i>
                        <span>Teacher Applications</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="#" class="menu-toggle">
                        <i class="fas fa-th-list"></i>
                        <span>Sections</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="grade11_sections.php">
                                <i class="fas fa-layer-group"></i>
                                <span>Grade 11</span>
                            </a>
                        </li>
                        <li>
                            <a href="grade12_sections.php">
                                <i class="fas fa-layer-group"></i>
                                <span>Grade 12</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Add Logout Menu Item -->
                <li class="menu-item">
                    <a href="../Admin-PHP/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-view">
            <div class="header">
                <img src="../Admin-IMAGES/school_dashboard.webp" alt="ISCP Campus">
            </div>

            <div class="content">
                <div class="section">
                    <h2>Total Students</h2>
                    <div class="stat-container">
                        <i class="fas fa-user-graduate stat-icon"></i>
                        <div class="stat-details">
                            <span class="stat-number" id="studentCount">0</span>
                            <p>Enrolled Students</p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2>Total Teachers</h2>
                    <div class="stat-container">
                        <i class="fas fa-chalkboard-teacher stat-icon"></i>
                        <div class="stat-details">
                            <span class="stat-number" id="teacherCount">0</span>
                            <p>Active Teachers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../Admin-JS/dashboard.js"></script>
</body>
</html>
