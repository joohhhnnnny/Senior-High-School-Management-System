<?php
session_start();
error_log("Session data in attendance.php: " . print_r($_SESSION, true));
error_log("Current session ID: " . session_id());

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    error_log("Session check failed. user_id: " . isset($_SESSION['user_id']) . ", user_type: " . ($_SESSION['user_type'] ?? 'not set'));
    header("Location: /CST5-PROJECT/FINAL-ADMIN/Portal-Main/main.php");
    exit();
}
require_once '../Student-php/fetchAttendance.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance</title>
    <link rel="stylesheet" href="../Student-css/attendance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
</head>
<body>
    <!-- Add Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Student Portal</h2>
        </div>
        <a href="profStud.php"><i class="fa-solid fa-user"></i>Student's Profile</a>
        <a href="attendance.php" class="active"><i class="fa-solid fa-clock"></i>Attendance</a>
        <a href="records.php"><i class="fa-regular fa-clipboard"></i>View Student's Record</a>
        <a href="../Student-php/handleLogout.php" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);">
            <i class="fa-solid fa-right-from-bracket"></i>Logout
        </a>    
    </div>

    <!-- Add Toggle Button -->
    <button class="toggle-btn" id="toggleBtn">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="attendance-container">
        <h1>Attendance Record</h1>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div class="calendar-container">
        <h1>Attendance Calendar</h1>
        
        <div class="filter-container">
            <label for="subjectFilter">Select Subject:</label>
            <select id="subjectFilter" class="form-control">
                <option value="all">All Subjects</option>
            </select>
        </div>

        <!-- Static legend -->
        <div class="legend-container">
            <div class="status-legend">
                <div class="legend-item">
                    <div class="circle present"></div>
                    <span>Present</span>
                </div>
                <div class="legend-item">
                    <div class="circle absent"></div>
                    <span>Absent</span>
                </div>
                <div class="legend-item">
                    <div class="circle excused"></div>
                    <span>Excused</span>
                </div>
                <div class="legend-item">
                    <div class="circle no-class"></div>
                    <span>No Class/Weekend</span>
                </div>
            </div>
        </div>

        <div class="calendar-header">
            <div class="calendar-nav">
                <button id="prevMonth" class="nav-btn">&lt;</button>
                <h2 id="currentMonthYear"></h2>
                <button id="nextMonth" class="nav-btn">&gt;</button>
            </div>
        </div>

        <table class="calendar">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody class="calendar-body">
            </tbody>
        </table>
    </div>

    <script src="../Student-js/attendance.js"></script>
</body>
</html>