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
    <title>Grades Management - ISCP</title>
    <link rel="stylesheet" href="../Teacher-css/teacher_dashboard.css">
    <link rel="stylesheet" href="../Teacher-css/grades_management.css">
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
            <div class="grades-container">
                <div class="section-header">
                    <h2>Grades Management</h2>
                    <div class="filters">
                        <input type="text" id="sectionInput" readonly placeholder="Section" />
                        <select id="subjectFilter" required>
                            <option value="">Select Subject</option>
                        </select>
                        <button class="load-btn" onclick="loadGrades()">
                            <i class="fas fa-sync-alt"></i> Load
                        </button>
                    </div>
                </div>

                <div class="grades-table">
                    <table id="gradesTable">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Midterm</th>
                                <th>Finals</th>
                                <th>Final Grade</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="gradesTableBody"></tbody>
                    </table>
                </div>

                <div class="bulk-actions">
                    <button class="save-all-btn" onclick="saveAllGrades()">
                        <i class="fas fa-save"></i> Save All Changes
                    </button>
                    <button class="export-btn" onclick="exportGrades()">
                        <i class="fas fa-file-export"></i> Export to Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingSpinner" class="loading-spinner">
        <div class="spinner"></div>
    </div>

    <script src="../Teacher-js/grades_management.js"></script>
</body>
</html>
