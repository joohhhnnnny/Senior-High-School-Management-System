<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Teacher Schedule Overview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: white;
            padding: 1em;
            text-align: center;
        }
        .container {
            margin: 2em;
        }
        .schedule-container {
            margin-top: 2em;
            padding: 2em;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .schedule-container h2 {
            margin-bottom: 1em;
        }
        .schedule-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
        }
        .schedule-container th, .schedule-container td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .schedule-container th {
            background-color: #4CAF50;
            color: white;
        }
        .schedule-container td {
            background-color: #f9f9f9;
        }
        .schedule-container td:hover {
            background-color: #f1f1f1;
        }
        .schedule-container button {
            background-color: #4CAF50;
            color: white;
            padding: 1em;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 1em;
        }
        .schedule-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Portal - Teacher Schedule Overview</h1>
</header>

<div class="container">
    <!-- Schedule View for All Teachers -->
    <div class="schedule-container" id="schedule-container">
        <h2>Teacher Class Schedules</h2>

        <!-- Table to Display All Teachers' Schedules -->
        <table>
            <thead>
                <tr>
                    <th>Teacher Name</th>
                    <th>Class Name</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Room</th>
                    <th>Semester</th>
                </tr>
            </thead>
            <tbody id="schedule-table-body">
                <!-- Schedule Rows will be dynamically generated here -->
            </tbody>
        </table>

        <button onclick="backToHome()">Back to Home</button>
    </div>
</div>

<script>
// Sample teacher schedules (this can be dynamically pulled from your database)
const teacherSchedules = [
    { teacher: "John Doe", class: "Math 101", day: "Monday", time: "10:00 AM", room: "101", semester: "Spring" },
    { teacher: "John Doe", class: "History 202", day: "Wednesday", time: "2:00 PM", room: "202", semester: "Spring" },
    { teacher: "Jane Smith", class: "Science 303", day: "Tuesday", time: "11:00 AM", room: "303", semester: "Fall" },
    { teacher: "Jane Smith", class: "Literature 404", day: "Thursday", time: "1:00 PM", room: "404", semester: "Fall" },
    { teacher: "Alice Brown", class: "English 105", day: "Monday", time: "9:00 AM", room: "105", semester: "Spring" },
    { teacher: "Alice Brown", class: "Chemistry 202", day: "Friday", time: "3:00 PM", room: "305", semester: "Spring" },
    // Add more schedules here...
];

// Generate the table to display teacher schedules
function generateScheduleTable() {
    const scheduleTableBody = document.getElementById('schedule-table-body');
    scheduleTableBody.innerHTML = '';  // Clear previous schedule data

    teacherSchedules.forEach((item) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.teacher}</td>
            <td>${item.class}</td>
            <td>${item.day}</td>
            <td>${item.time}</td>
            <td>${item.room}</td>
            <td>${item.semester}</td>
        `;
        scheduleTableBody.appendChild(row);
    });
}

// Go back to the home page or another section (if needed)
function backToHome() {
    window.location.href = "teacher.html";  // Redirect to another page or home
}

// Initialize the schedule table
generateScheduleTable();
</script>

</body>
</html>
