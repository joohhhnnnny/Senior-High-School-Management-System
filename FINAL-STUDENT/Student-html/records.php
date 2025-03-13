<?php
session_start();
require_once '../Student-php/getStudentRecords.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /CST5-PROJECT/FINAL-ADMIN/Portal-Main/main.php");
    exit();
}

$records = getStudentRecords($_SESSION['user_id']);

// Debug session information
error_log("Records.php - Session ID: " . session_id());
error_log("Records.php - Session Data: " . print_r($_SESSION, true));

$studentID = $_SESSION['user_id'];
$records = getStudentRecords($studentID);

// Debug
if (isset($records['message'])) {
    error_log("Records message: " . $records['message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records</title>
    <link rel="stylesheet" href="../Student-css/records.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Student Portal</h2>
        </div>
        <a href="profStud.php"><i class="fa-solid fa-user"></i>Student's Profile</a>
        <a href="attendance.php"><i class="fa-solid fa-clock"></i>Attendance</a>
        <a href="records.php" class="active"><i class="fa-regular fa-clipboard"></i>View Student's Record</a>
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
        <div class="section">
            <h1>Academic Records</h1>
            <table class="records-table">
                <thead>
                    <tr>
                        <th>Subject Title</th>
                        <th>Year Level</th>
                        <th>Semester</th>
                        <th>Final Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($records['status']) {
                        foreach ($records['data'] as $record) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($record['subject_title'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($record['yearLevel'] ?? '') . "</td>";
                            echo "<td>" . ucfirst(htmlspecialchars($record['semester'] ?? '')) . "</td>";
                            echo "<td>" . ($record['final_grade'] ? number_format($record['final_grade'], 2) : '') . "</td>";
                            echo "<td>" . htmlspecialchars($record['remarks'] ?? '') . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center;'>" . ($records['message'] ?? 'No records found') . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../Student-js/records.js"></script>
</body>
</html>
