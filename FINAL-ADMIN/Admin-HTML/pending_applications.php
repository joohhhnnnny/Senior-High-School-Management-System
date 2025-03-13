<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Applications</title>
    <link rel="stylesheet" href="../Admin-CSS/pending_applications.css">
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
                <li class="menu-item active">
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
                    <!-- Remove 'show' class from submenu -->
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

    <!-- Wrap existing content in main-content div -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="applications-management">
                <div class="search-section">
                    <h2>Applications Management</h2>
                    <div class="search-container">
                        <div class="search-input">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search by Name, ID or Email">
                        </div>
                        <div class="filter-group">
                            <select id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="quick-stats" id="statsContainer">
                    <!-- Stats will be populated dynamically -->
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3>Teaching Position Applications</h3>
                        <div class="header-actions">
                            <button class="export-btn">
                                <i class="fas fa-file-export"></i> Export List
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Application ID</th>
                                    <th>Applicant Info</th>
                                    <th>Position</th>
                                    <th>Date Applied</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="applicationsTableBody">
                                <!-- Table content will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="professorModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Professor Application Details</h2>
                <div id="professorDetails" class="details-container">
                    <!-- Details will be populated dynamically -->
                </div>
            </div>
        </div>

        <div id="customAlert" class="custom-alert">
            <p class="message"></p>
        </div>
    </div>

    <script src="../Admin-JS/pending_applications.js"></script>
</body>
</html>
