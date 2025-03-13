<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session and debug logging
session_start();
error_log("Session data in profStud.php: " . print_r($_SESSION, true));
error_log("Current session ID: " . session_id());
error_log("Script path: " . __FILE__);
error_log("Document root: " . $_SERVER['DOCUMENT_ROOT']);
error_log("Include path: " . get_include_path());

// Check session before attempting to include files
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    error_log("Session check failed. user_id: " . isset($_SESSION['user_id']) . ", user_type: " . ($_SESSION['user_type'] ?? 'not set'));
    header("Location: /FINAL-ADMIN/Portal-Main/main.php"); // Modified path for InfinityFree
    exit();
}

// Try to include fetchStudentProfile.php with error handling
try {
    $fetchProfilePath = __DIR__ . '/../Student-php/fetchStudentProfile.php';
    error_log("Attempting to include file from: " . $fetchProfilePath);
    
    if (!file_exists($fetchProfilePath)) {
        throw new Exception("fetchStudentProfile.php not found at: " . $fetchProfilePath);
    }
    
    require_once $fetchProfilePath;
} catch (Exception $e) {
    error_log("Error including profile file: " . $e->getMessage());
    $error_message = "System Error: Unable to load profile system. Details have been logged.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student's Profile</title>
    <link rel="stylesheet" href="../Student-css/profStud.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Student Portal</h2>
        </div>
        <a href="profStud.php" class="active"><i class="fa-solid fa-user"></i>Student's Profile</a>
        <a href="attendance.php"><i class="fa-solid fa-clock"></i>Attendance</a>
        <a href="records.php"><i class="fa-regular fa-clipboard"></i>View Student's Record</a>
        <a href="../Student-php/handleLogout.php" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);">
            <i class="fa-solid fa-right-from-bracket"></i>Logout
        </a>
    </div>

    <!-- Toggle Button -->
    <button class="toggle-btn" id="toggleBtn">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="content">
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <p>Error: <?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php elseif ($student): ?>
            <div class="header">
                <h1>Student Profile</h1>
                <p class="student-id">Student ID: <?php echo $student['studentID']; ?></p>
            </div>

            <div class="profile-container">
                <div class="profile-pic-container">
                    <div class="profile-pic-wrapper">
                        <img src="../Student-images/default-profile.jpg" 
                             alt="Student Profile Picture" class="profile-pic">
                    </div>
                </div>

                <div class="profile-info">
                    <div class="info-section personal-info">
                        <h2><i class="fas fa-user"></i> Personal Information</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Full Name</label>
                                <p><?php echo $student['fullname']; ?></p>
                            </div>
                            <div class="info-item">
                                <label>Student ID</label>
                                <p><?php echo $student['studentID']; ?></p>
                            </div>
                            <div class="info-item">
                                <label>Year Level</label>
                                <p>Grade <?php echo $student['yearLevel']; ?></p>
                            </div>
                            <div class="info-item">
                                <label>Strand</label>
                                <p><?php echo $student['year_strand']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="info-section contact-info">
                        <h2><i class="fas fa-address-card"></i> Contact Details</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Email</label>
                                <p><?php echo $student['email']; ?></p>
                            </div>
                            <div class="info-item">
                                <label>Contact Number</label>
                                <p><?php echo $student['phoneNumber']; ?></p>
                            </div>
                            <div class="info-item">
                                <label>Address</label>
                                <p><?php echo $student['address']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="error-message">
                <p>Unable to load student profile data.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="../Student-js/profStud.js"></script>
</body>
</html>