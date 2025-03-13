document.addEventListener('DOMContentLoaded', function() {
    console.log('Attendance Management JS loaded'); // Debug line
    loadSubjects();
    initializeEventListeners();
});

function initializeEventListeners() {
    document.getElementById('subjectFilter').addEventListener('change', loadStudentAttendance);
    document.getElementById('dateFilter').addEventListener('change', loadStudentAttendance);
}

async function loadSubjects() {
    try {
        const response = await fetch('../Teacher-php/get_attendance_data.php');
        const data = await response.json();
        
        if (!data.subjects || !Array.isArray(data.subjects)) {
            throw new Error('Invalid subjects data');
        }
        
        const subjectSelect = document.getElementById('subjectFilter');
        subjectSelect.innerHTML = '<option value="">Select Subject</option>' +
            data.subjects.map(subject => 
                `<option value="${subject.id}" data-section="${subject.section_name}">
                    ${subject.subject_title} (${subject.section_name})
                </option>`
            ).join('');
    } catch (error) {
        showError('Error loading subjects');
        console.error('Error:', error);
    }
}

async function loadStudentAttendance() {
    const subjectId = document.getElementById('subjectFilter').value;
    const date = document.getElementById('dateFilter').value;
    
    if (!subjectId || !date) {
        showError('Please select both subject and date');
        return;
    }

    showLoading(true);
    
    try {
        const response = await fetch(`../Teacher-php/get_attendance_data.php?subject_id=${subjectId}&date=${date}`);
        const data = await response.json();
        
        if (!response.ok) throw new Error(data.error || 'Failed to load attendance');
        
        updateAttendanceTable(data.attendance);
    } catch (error) {
        showError('Failed to load attendance data');
        console.error('Error:', error);
    } finally {
        showLoading(false);
    }
}

function updateAttendanceTable(students) {
    const tbody = document.getElementById('attendanceTableBody');
    
    if (!students || !students.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center">No students found in this section</td></tr>`;
        return;
    }

    tbody.innerHTML = students.map(student => {
        const strand = student.year_strand.split('-')[1] || student.year_strand;
        const statusClass = student.status !== 'Not Recorded' ? `status-${student.status.toLowerCase()}` : '';
        
        return `
        <tr data-student-id="${student.studentID}">
            <td>${student.studentID}</td>
            <td>${student.fullname}</td>
            <td>${student.yearLevel}-${strand}</td>
            <td>
                <select class="attendance-status" onchange="updateRowStatus(this)">
                    <option value="Present" ${student.status === 'Present' ? 'selected' : ''}>Present</option>
                    <option value="Absent" ${student.status === 'Absent' ? 'selected' : ''}>Absent</option>
                    <option value="Excused" ${student.status === 'Excused' ? 'selected' : ''}>Excused</option>
                </select>
            </td>
            <td>
                <input type="text" class="remarks-input" 
                       value="${student.remarks || ''}" 
                       onchange="markAsChanged(this)"
                       placeholder="Add remarks">
            </td>
            <td>
                <button class="btn-save" onclick="saveIndividualAttendance('${student.studentID}')">
                    <i class="fas fa-save"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

function updateRowStatus(selectElement) {
    const row = selectElement.closest('tr');
    // Remove all existing status classes
    row.classList.remove('status-present', 'status-absent', 'status-excused');
    // Add the new status class
    row.classList.add(`status-${selectElement.value.toLowerCase()}`);
    markAsChanged(selectElement);
}

async function saveAllAttendance() {
    const subjectId = document.getElementById('subjectFilter').value;
    const subjectTitle = document.querySelector(`#subjectFilter option[value="${subjectId}"]`).textContent.split('(')[0].trim();
    const date = document.getElementById('dateFilter').value;
    
    const rows = document.querySelectorAll('#attendanceTableBody tr[data-student-id]');
    const attendanceData = Array.from(rows).map(row => ({
        studentID: row.dataset.studentId,
        status: row.querySelector('.attendance-status').value,
        remarks: row.querySelector('.remarks-input').value,
        semester: 'first',
        yearLevel: row.querySelector('td:nth-child(3)').textContent.split('-')[0]
    }));

    try {
        const response = await fetch('../Teacher-php/save_attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                subject_title: subjectTitle,
                date: date,
                attendance: attendanceData
            })
        });

        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Failed to save attendance');
        
        showSuccess('Attendance saved successfully');
        loadStudentAttendance();
    } catch (error) {
        showError('Failed to save attendance');
        console.error('Error:', error);
    } finally {
        showLoading(false);
    }
}

async function saveIndividualAttendance(studentId) {
    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
    if (!row) {
        console.error('Row not found for student ID:', studentId);
        return;
    }

    const subjectId = document.getElementById('subjectFilter').value;
    const subjectTitle = document.querySelector(`#subjectFilter option[value="${subjectId}"]`).textContent.split('(')[0].trim();
    const date = document.getElementById('dateFilter').value;
    
    const attendanceData = {
        subject_title: subjectTitle,
        date: date,
        attendance: [{
            studentID: studentId,
            status: row.querySelector('.attendance-status').value,
            remarks: row.querySelector('.remarks-input').value,
            semester: 'first',
            yearLevel: row.querySelector('td:nth-child(3)').textContent.split('-')[0]
        }]
    };

    showLoading(true);
    console.log('Saving attendance data:', attendanceData); // Debug line
    
    try {
        const response = await fetch('../Teacher-php/save_attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(attendanceData)
        });

        const text = await response.text();
        console.log('Server response:', text); // Debug line
        
        try {
            const result = JSON.parse(text);
            if (!response.ok) throw new Error(result.error || 'Failed to save attendance');
            
            showSuccess('Attendance saved successfully');
            row.classList.remove('changed');
            
            // Update the row status class
            const status = row.querySelector('.attendance-status').value.toLowerCase();
            row.classList.remove('status-present', 'status-absent', 'status-excused');
            row.classList.add(`status-${status}`);
        } catch (e) {
            console.error('Parse error:', e);
            throw new Error('Invalid server response');
        }
    } catch (error) {
        showError('Failed to save attendance: ' + error.message);
        console.error('Save error:', error);
    } finally {
        showLoading(false);
    }
}

function markAsChanged(element) {
    const row = element.closest('tr');
    row.classList.add('changed');
}

function showLoading(show) {
    document.getElementById('loadingSpinner').style.display = show ? 'flex' : 'none';
}

function showError(message) {
    alert(message); // Replace with better error handling if needed
}

function showSuccess(message) {
    alert(message); // Replace with better success handling if needed
}
