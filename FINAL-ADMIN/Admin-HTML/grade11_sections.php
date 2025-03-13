<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade 11 Sections</title>
    <link rel="stylesheet" href="../Admin-CSS/grade11_sections.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Add Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../Admin-IMAGES/iscp-logo.webp" alt="ISCP Logo" class="logo">
            <h2>ISCP Admin</h2>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="menu-item">
                    <a href="dashboard.php">
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
                
                <li class="menu-item active">
                    <a href="#" class="menu-toggle">
                        <i class="fas fa-th-list"></i>
                        <span>Sections</span>
                        <i class="fas fa-chevron-down arrow"></i>
                    </a>
                    <ul class="submenu show">
                        <li>
                            <a href="grade11_sections.php" class="active">
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

    <!-- Wrap existing content in main-content div -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="sections-management">
                <!-- Header Section -->
                <div class="header-content">
                    <h2>Grade 11 Schedule</h2>
                    <p class="subtitle">Manage Grade 11 sections and schedules</p>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-group">
                        <label>Strand:</label>
                        <select id="strandFilter" class="filter-select">
                            <option value="">Select Strand</option>
                            <option value="HUMSS" selected>Humanities & Social Sciences</option>
                            <option value="AAD">Arts and Design</option>
                            <option value="ICT">Information & Communication Technology</option>
                        </select>
                    </div>
                    <div class="schedule-legend">
                        <div class="legend-item">
                            <span class="legend-color major-legend"></span>
                            <span class="legend-text">Major Subjects</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color minor-legend"></span>
                            <span class="legend-text">Minor Subjects</span>
                        </div>
                    </div>
                </div>

                <!-- Schedule Containers -->
                <div class="schedules-wrapper">
                    <!-- First Semester -->
                    <div class="schedule-container" data-semester="first">
                        <h3 class="semester-title">First Semester Schedule</h3>
                        <div class="table-responsive">
                            <table class="schedule-table" id="firstSemesterTable">
                                <colgroup>
                                    <col style="width: 80px">
                                    <col span="5" style="width: calc((100% - 80px) / 5)">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="time-column">Time</th>
                                        <th>Monday</th>
                                        <th>Tuesday</th>
                                        <th>Wednesday</th>
                                        <th>Thursday</th>
                                        <th>Friday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Time slots will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Second Semester -->
                    <div class="schedule-container" data-semester="second">
                        <h3 class="semester-title">Second Semester Schedule</h3>
                        <div class="table-responsive">
                            <table class="schedule-table" id="secondSemesterTable">
                                <colgroup>
                                    <col style="width: 80px">
                                    <col span="5" style="width: calc((100% - 80px) / 5)">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="time-column">Time Slot</th>
                                        <th>Monday</th>
                                        <th>Tuesday</th>
                                        <th>Wednesday</th>
                                        <th>Thursday</th>
                                        <th>Friday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Time slots will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../Admin-JS/grade11_sections.js"></script>
</body>
</html>