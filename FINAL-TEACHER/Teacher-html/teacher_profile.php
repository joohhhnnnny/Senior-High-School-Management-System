<?php
session_start();

// Debug section - remove in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simplified session check
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: ../../FINAL-ADMIN/Portal-Main/main.php");
    exit();
}

// Debug log - remove in production
error_log("Current session data: " . print_r($_SESSION, true));

// Ensure phone_number is set
if (!isset($_SESSION['phone_number'])) {
    // Use the correct path to connection file
    include '../Teacher-php/conn.php';
    
    $email = $_SESSION['professor_email'];
    $query = "SELECT phoneNumber FROM professor WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['phone_number'] = $row['phoneNumber'] ?? 'Not provided';
    } else {
        $_SESSION['phone_number'] = 'Not provided';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile - ISCP</title>
    <link rel="stylesheet" href="../Teacher-css/teacher_dashboard.css">
    <link rel="stylesheet" href="../Teacher-css/teacher_profile.css">
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
            <div class="profile-container">
                <h1 class="page-title">Teacher Profile</h1>
                
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-image-container">
                            <img src="../Teacher-IMAGES/default-profile.jpg" alt="Teacher Profile">
                        </div>
                        
                        <div class="teacher-details">
                            <h2><?php echo htmlspecialchars($_SESSION['professor_name']); ?></h2>
                            <span class="teacher-role">Faculty Member</span>
                        </div>
                    </div>

                    <div class="profile-content">
                        <form id="profileForm" onsubmit="return saveProfile(event)"></form>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="professorId">Professor ID</label>
                                    <input type="text" id="professorId" value="<?php echo htmlspecialchars($_SESSION['professor_id'] ?? ''); ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="fullName">Full Name</label>
                                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($_SESSION['professor_name']); ?>" required readonly>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['professor_email']); ?>" required readonly>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           value="<?php echo htmlspecialchars($_SESSION['phone_number'] ?? 'Not provided'); ?>" 
                                           readonly>
                                </div>
                            </div>

                            <div class="form-actions"></div>
                                <button type="submit" class="save-btn">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingSpinner" class="loading-spinner">
        <div class="spinner"></div>
    </div>

    <script src="../Teacher-js/teacher_profile.js"></script>
</body>
</html>
