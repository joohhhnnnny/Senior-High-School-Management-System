<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Admin-CSS/student_management.css">
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
                <li class="menu-item">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-item active"> <!-- Changed to active -->
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
        <div class="container-fluid">
            <div class="student-management">
                <div class="search-section">
                    <h2>Student Management</h2>
                    <div class="search-container">
                        <div class="search-input">
                            <i class="fas fa-search"></i>
                            <input 
                                type="text" 
                                id="search" 
                                placeholder="   Search students..."
                                autocomplete="off"
                                spellcheck="false"
                            >
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
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3>Student Records</h3>
                        <!-- Removed the add-student-btn -->
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Full Name</th>
                                    <th>Year Level</th>
                                    <th>Strand</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Phone Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <!-- Data will be dynamically inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update the edit modal structure -->
        <div id="editModal" class="student-modal">
            <div class="modal-wrapper">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Edit Student</h2>
                        <button type="button" class="close-btn" onclick="closeEditModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="editStudentForm" novalidate>
                            <!-- Add data-field attributes to match database fields -->
                            <input type="hidden" id="editId" data-field="id">
                            <div class="form-group">
                                <label for="editStudentId">Student ID</label>
                                <input type="text" id="editStudentId" data-field="studentID" readonly class="readonly-input">
                            </div>
                            <div class="form-group">
                                <label for="editFullname">Full Name *</label>
                                <input type="text" id="editFullname" data-field="fullname" required>
                            </div>
                            <div class="form-group">
                                <label for="editYearLevel">Year Level</label>
                                <input type="text" id="editYearLevel" data-field="yearLevel" readonly class="readonly-input">
                            </div>
                            <div class="form-group">
                                <label for="editStrand">Strand</label>
                                <input type="text" id="editStrand" data-field="strand" readonly class="readonly-input">
                            </div>
                            <div class="form-group">
                                <label for="editEmail">Email Address</label>
                                <input type="email" id="editEmail" data-field="email" readonly class="readonly-input">
                            </div>
                            <div class="form-group">
                                <label for="editAddress">Address *</label>
                                <input type="text" id="editAddress" data-field="address" required>
                            </div>
                            <div class="form-group">
                                <label for="editPhone">Phone Number *</label>
                                <input type="tel" id="editPhone" data-field="phoneNumber" required 
                                       pattern="^(09|\+639)\d{9}$" 
                                       title="Enter a valid Philippine phone number (e.g., 09123456789)">
                            </div>
                            <div class="modal-buttons">
                                <button type="button" class="save-btn" id="saveChangesBtn">Save Changes</button>
                                <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update the view modal structure -->
        <div id="viewModal" class="student-modal">
            <div class="modal-wrapper">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Student Details</h2>
                        <button type="button" class="close-btn" onclick="closeViewModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="student-info">
                            <h3>Personal Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Student ID:</label>
                                    <span id="viewStudentId"></span>
                                </div>
                                <div class="info-item">
                                    <label>Full Name:</label>
                                    <span id="viewFullname"></span>
                                </div>
                                <div class="info-item">
                                    <label>Year Level:</label>
                                    <span id="viewYearLevel"></span>
                                </div>
                                <div class="info-item">
                                    <label>Strand:</label>
                                    <span id="viewStrand"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Update the view modal filters design -->
                        <div class="student-grades">
                            <div class="section-header">
                                <h3>Academic Performance</h3>
                                <div class="filter-container">
                                    <select id="gradesFilter" class="custom-select">
                                        <option value="all">All Semesters</option>
                                        <option value="first">First Semester</option>
                                        <option value="second">Second Semester</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Semester</th>
                                            <th>Midterm</th>
                                            <th>Finals</th>
                                            <th>Final Grade</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewGradesBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="student-attendance">
                            <div class="section-header">
                                <h3>Attendance Record</h3>
                                <div class="filter-container">
                                    <select id="attendanceSemesterFilter" class="custom-select">
                                        <option value="all">All Semesters</option>
                                        <option value="first">First Semester</option>
                                        <option value="second">Second Semester</option>
                                    </select>
                                    <select id="attendanceSubjectFilter" class="custom-select">
                                        <option value="all">All Subjects</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewAttendanceBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keep only the essential script -->
        <script src="../Admin-JS/student_management.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.group('Student Management Initialization');
                console.log('DOM loaded, initializing student management...');
                
                if (window.StudentManagement) {
                    window.StudentManagement.loadStudents()
                        .then(() => {
                            console.log('Students loaded, initializing filters...');
                            window.StudentManagement.initializeFilters();
                            window.StudentManagement.initializeStyles();
                            window.StudentManagement.initializeEventListeners();
                        })
                        .catch(error => {
                            console.error('Error initializing student management:', error);
                        });
                } else {
                    console.error('StudentManagement object not found!');
                }
                
                console.groupEnd();
            });
        </script>
    </div>
</body>
</html>
