<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management</title>
    <link rel="stylesheet" href="../Admin-CSS/teacher_management.css">
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
                
                <li class="menu-item active">
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

    <!-- Wrap existing content in main-content div -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="teacher-management">
                <!-- Search Section -->
                <div class="search-section">
                    <h2>Teacher Management</h2>
                    <div class="search-container">
                        <div class="search-input">
                            <i class="fas fa-search"></i>
                            <input type="text" id="search" placeholder="Search by Name or ID">
                        </div>
                    </div>
                    <div id="searchResultsCount" style="display: none;"></div>
                </div>

                <!-- Table Container -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Teacher Records</h3>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Professor ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teacherTableBody">
                                <!-- Loading state placeholder -->
                                <tr class="loading-row">
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <p>Loading teachers...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Teacher</h3>
                    <button type="button" class="close" onclick="TeacherManagement.closeEditModal()">&times;</button>
                </div>
                <form id="editProfessorForm">
                    <input type="hidden" id="editId">
                    <div class="form-group">
                        <label>Professor ID:</label>
                        <span id="displayProfessorId"></span>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Full Name:</label>
                        <input type="text" id="fullname" name="fullname" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number:</label>
                        <input type="tel" id="phoneNumber" name="phoneNumber">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="TeacherManagement.closeEditModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedule Modal -->
        <div id="scheduleModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-calendar-alt"></i> Assign Schedule</h2>
                    <span class="close" onclick="closeScheduleModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="assignScheduleForm" class="schedule-form">
                        <!-- Professor details section -->
                        <div class="professor-info">
                            <input type="hidden" name="professor_id" id="scheduleProfessorId">
                            <div class="info-group">
                                <label>Professor Name:</label>
                                <span id="scheduleProfessorName"></span>
                            </div>
                            <div class="info-group">
                                <label>Professor ID:</label>
                                <span id="scheduleProfessorIdDisplay"></span>
                            </div>
                        </div>
                        
                        <!-- Rest of your form fields -->
                        <div class="form-section">
                            <h3>Class Details</h3>
                            <div class="input-grid">
                                <div class="form-group">
                                    <label for="strandSelect">Strand</label>
                                    <select id="strandSelect" required>
                                        <option value="">Select Strand</option>
                                        <option value="ICT">ICT</option>
                                        <option value="HUMSS">HUMSS</option>
                                        <option value="AAD">AAD</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="yearLevelSelect">Year Level</label>
                                    <select id="yearLevelSelect" required>
                                        <option value="">Select Year Level</option>
                                        <option value="11">Grade 11</option>
                                        <option value="12">Grade 12</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="semesterSelect">Semester</label>
                                    <select id="semesterSelect" required>
                                        <option value="">Select Semester</option>
                                        <option value="first">First Semester</option>
                                        <option value="second">Second Semester</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="subjectSelect">Subject</label>
                                    <select id="subjectSelect" required disabled>
                                        <option value="">Select Subject</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Schedule Details</h3>
                            <div class="input-grid">
                                <div class="form-group">
                                    <label for="daySelect">Day</label>
                                    <select id="daySelect" required>
                                        <option value="">Select Day</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                    </select>
                                </div>

                                <div class="form-group time-group">
                                    <label for="timeStart">Time Start</label>
                                    <input type="time" id="timeStart" required>
                                </div>

                                <div class="form-group time-group">
                                    <label for="timeEnd">Time End</label>
                                    <input type="time" id="timeEnd" required>
                                </div>

                                <div class="form-group">
                                    <label for="roomInput">Room</label>
                                    <input type="text" id="roomInput" required placeholder="Enter room number">
                                </div>
                            </div>
                        </div>

                        <!-- Update the modal footer inside the Schedule Modal -->
                        <div class="modal-footer">
                            <button type="submit" class="btn-primary" id="addScheduleBtn">
                                <i class="fas fa-save"></i>
                                Save Schedule
                            </button>
                            <button type="button" class="btn-secondary" onclick="closeScheduleModal()">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="customAlert" class="custom-alert">
            <p class="message"></p>
        </div>
    </div>

    <script src="../Admin-JS/teacher_management.js"></script>
    <script>
        // Add this after your modal HTML
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('editModal');
            if (modal) {
                console.log('Edit modal found, contents:', modal.innerHTML);
            } else {
                console.error('Edit modal not found in DOM');
            }
        });
    </script>
</body>
</html>