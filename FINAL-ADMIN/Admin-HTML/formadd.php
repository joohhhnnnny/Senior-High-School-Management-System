<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: black;
            color: white;
            padding: 20px;
            height: 60px;
            text-align: center;
        }
        .container {
            width: 89%;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            text-align: center;
            color: black;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input[type="radio"] {
            width: auto;
            margin-right: 10px;
        }
        .form-group input[type="file"] {
            width: auto;
        }
        .form-group button {
            padding: 15px;
            background-color: #578FCA;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .form-group button:hover {
            background-color: #1d496d;
            
        }
    </style>
</head>
<body>

<header>
    <h1>ISCP Official Student Form</h1>
</header>

<div class="container">
    <h2>Student Details</h2>
    <form id="enrollmentForm">
        <!-- Student Name -->
        <div class="form-group">
            <label for="studentName">Full Name:</label>
            <input type="text" id="studentName" name="studentName" required placeholder="Enter Full Name">
        </div>
        
        <!-- Auto-generated Student ID -->
        <div class="form-group">
            <label for="studentId">Student ID (Auto-generated):</label>
            <input type="text" id="studentId" name="studentId" disabled placeholder="Student ID" value="ID-{{randomID()}}">
        </div>

        <!-- Year Level -->
        <div class="form-group">
            <label for="yearLevel">Year Level:</label>
            <select id="yearLevel" name="yearLevel" required onchange="updateSubjects()">
                <option value="1">11th Grade</option>
                <option value="2">12th Grade</option>
                
            </select>
        </div>

        <!-- Semester -->
        <div class="form-group">
            <label for="semester">Semester:</label>
            <select id="semester" name="semester" required>
                <option value="1">1st Semester</option>
                <option value="2">2nd Semester</option>
            </select>
        </div>

        <!-- Subjects based on Year Level -->
        <div class="form-group" id="subjectsGroup">
            <label for="subjects">Subjects Assigned:</label>
            <textarea id="subjects" name="subjects" rows="4" disabled placeholder="Subjects will be assigned based on Year Level"></textarea>
        </div>

        <!-- Address -->
        <div class="form-group">
            <label for="address">Address:</label>
            <input type="address" id="address" name="address" required placeholder="Enter Email Address">
        </div>
        <!-- Date of Birth -->
        <div class="form-group">
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required placeholder="Enter Email Address">
        </div>

        <!-- Sex -->
        <div class="form-group">
            <label>Gender:</label>
            <input type="radio" id="male" name="gender" value="Male">
            <label for="male">Male</label>
            <input type="radio" id="female" name="gender" value="Female">
            <label for="female">Female</label>
        </div>

        <!-- Upload Documents -->
        <div class="form-group">
            <label for="documents">Upload Documents (Form 137, Birth Certificate, etc.):</label>
            <input type="file" id="documents" name="documents" multiple>
        </div>

        <!-- Add Student Button -->
        <div class="form-group">
            <button type="submit" id="addStudentButton">Add Student</button>
        </div>
    </form>
</div>

<script>
// Sample subjects per year level
const subjectsByYearLevel = {
    1: ['Math 101', 'English 101', 'Science 101', 'History 101'],
    2: ['Math 102', 'English 102', 'Physics 101', 'History 102'],
    3: ['Math 201', 'Biology 101', 'Chemistry 101', 'History 201'],
    4: ['Math 301', 'Biology 201', 'Chemistry 201', 'History 301']
};

// Auto-generate student ID (for illustration, use a random number generator here)
function randomID() {
    return 'ID-' + Math.floor(Math.random() * 1000000);
}

// Update subjects based on selected year level
function updateSubjects() {
    const yearLevel = document.getElementById('yearLevel').value;
    const subjects = subjectsByYearLevel[yearLevel];
    const subjectsTextarea = document.getElementById('subjects');

    subjectsTextarea.value = subjects.join('\n');
}

// Handle form submission
document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const studentName = document.getElementById('studentName').value;
    const studentId = document.getElementById('studentId').value;
    const yearLevel = document.getElementById('yearLevel').value;
    const semester = document.getElementById('semester').value;
    const subjects = document.getElementById('subjects').value;
    const dob = document.getElementById('dob').value;
    const email = document.getElementById('email').value;
    const gender = document.querySelector('input[name="gender"]:checked') ? document.querySelector('input[name="gender"]:checked').value : '';
    const documents = document.getElementById('documents').files;

    if (!gender) {
        alert('Please select a gender.');
        return;
    }

    alert(`Student Added:\nName: ${studentName}\nStudent ID: ${studentId}\nYear Level: ${yearLevel}\nSemester: ${semester}\nSubjects:\n${subjects}\nDate of Birth: ${dob}\nEmail: ${email}\nGender: ${gender}`);

    // You can send this data to your server using AJAX, or handle it as needed
});
</script>

</body>
</html>
