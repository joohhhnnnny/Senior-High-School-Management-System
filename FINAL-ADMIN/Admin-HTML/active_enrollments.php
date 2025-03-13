<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Enrollments</title>
    <!-- Add a version number to prevent caching issues -->
    <link rel="stylesheet" href="../Admin-CSS/active_enrollments.css?v=1.0">
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
                
                <li class="menu-item active"> <!-- Active state for current page -->
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

    <!-- Give the main content a unique ID -->
    <div id="enrollment-management-container" class="main-content">
        <!-- Ensure this div appears only once -->
        <div class="container-fluid">
            <div class="enrollment-management">
                <div class="search-section">
                    <h2>Enrollment Management</h2>
                    <div class="search-container">
                        <div class="search-input">
                            <i class="fas fa-search"></i>
                            <input type="text" id="search" placeholder="Search by Student ID">
                        </div>
                        <div class="filter-group">
                            <select id="year-level">
                                <option value="">All Year Levels</option>
                                <option value="11">Grade 11</option>
                                <option value="12">Grade 12</option>
                            </select>
                            <select id="strand">
                                <option value="">All Strands</option>
                                <option value="ICT">ICT</option>
                                <option value="HUMSS">HUMSS</option>
                                <option value="AAD">AAD</option>
                            </select>
                            <select id="application-status">
                                <option value="">All Status</option>
                                <option value="pending">Pending Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="quick-stats">
                    <div class="stat-card">
                        <i class="fas fa-user-clock"></i>
                        <div class="stat-info">
                            <h3>Pending Applications</h3>
                            <span id="pending-count">0</span>
                            <p>Awaiting Review</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-check"></i>
                        <div class="stat-info">
                            <h3>Approved Today</h3>
                            <span id="approved-count">0</span>
                            <p>New Enrollees</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-chart-line"></i>
                        <div class="stat-info">
                            <h3>Total Applications</h3>
                            <span id="total-applications-count">0</span>
                            <p>This Week</p>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3>Enrollment Applications</h3>
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
                                    <th>StudentID</th>
                                    <th>Name</th>
                                    <th>Year Level</th>
                                    <th>Strand</th>
                                    <th>Status</th>
                                    <th>Date Applied</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="applications-tbody">
                                <!-- Data will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="studentModal" class="student-modal">
        <div class="modal-wrapper">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Student Details</h2>
                    <button type="button" class="close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="studentDetails">
                        <!-- Content will be dynamically inserted here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update the image modal structure -->
    <div id="imageModal" class="image-modal">
        <div class="modal-controls">
            <div class="zoom-controls">
                <button class="control-btn zoom-in" title="Zoom In">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button class="control-btn zoom-out" title="Zoom Out">
                    <i class="fas fa-search-minus"></i>
                </button>
                <button class="control-btn reset" title="Reset">
                    <i class="fas fa-undo"></i>
                </button>
            </div>
            <button class="control-btn close-btn" title="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="zoom-container">
            <img id="expandedImg" src="" alt="Expanded Image" draggable="false">
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <!-- Alert Container -->
    <div id="customAlert" class="custom-alert">
        <p class="message"></p>
    </div>

    <!-- Move scripts to bottom and add version number -->
    <script src="../Admin-JS/active_enrollments.js?v=1.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Remove duplicate enrollment management containers
            const containers = document.querySelectorAll('.enrollment-management');
            if (containers.length > 1) {
                for (let i = 1; i < containers.length; i++) {
                    containers[i].remove();
                }
            }
        });
    </script>
</body>
</html>
