document.addEventListener('DOMContentLoaded', () => {
    const currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    let selectedSubject = 'all';

    async function fetchAttendanceData(month, year, subject = 'all') {
        console.log('Fetching attendance for:', { month, year, subject });
        try {
            const response = await fetch(`../Student-php/get_attendance.php?month=${month}&year=${year}&subject=${subject}`);
            const rawResponse = await response.text();
            console.log('Raw server response:', rawResponse);
            
            const data = JSON.parse(rawResponse);
            
            if (!data.success) {
                throw new Error(`Server error: ${data.error}\nDetails: ${JSON.stringify(data.debug)}`);
            }

            // Update subject filter options while preserving current selection
            const subjectFilter = document.getElementById('subjectFilter');
            const currentSelection = subjectFilter.value;
            subjectFilter.innerHTML = ''; // Clear existing options
            
            // Add "All Subjects" option
            const allOption = document.createElement('option');
            allOption.value = 'all';
            allOption.textContent = 'All Subjects';
            allOption.selected = currentSelection === 'all';
            subjectFilter.appendChild(allOption);
            
            // Add subject options from response
            data.subjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.subject_title;
                option.textContent = subject.subject_title;
                option.selected = currentSelection === subject.subject_title;
                subjectFilter.appendChild(option);
            });

            const processedData = {
                attendanceCount: data.data.length,
                subjectsCount: data.subjects.length,
                attendanceData: data.data,
                debug: data.debug
            };

            console.log('Processed response:', processedData);
            updateAttendanceTable(processedData.attendanceData);
            return processedData.attendanceData;

        } catch (error) {
            console.error('Attendance fetch error:', error);
            return [];
        }
    }

    function createCalendar(month, year, attendanceData = []) {
        console.log('Creating calendar with data:', {
            month,
            year,
            selectedSubject,
            attendanceData
        });
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"];

        document.getElementById('currentMonthYear').textContent = `${monthNames[month]} ${year}`;

        // Calendar creation
        const calendarBody = document.querySelector('.calendar-body');
        calendarBody.innerHTML = '';
        const today = new Date();

        let date = 1;
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('tr');
            
            for (let j = 0; j < 7; j++) {
                const cell = document.createElement('td');
                
                if (i === 0 && j < startingDay) {
                    cell.classList.add('no-class');
                } else if (date > daysInMonth) {
                    break;
                } else {
                    const dayDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                    const dateDiv = document.createElement('div');
                    dateDiv.className = 'date-number';
                    dateDiv.textContent = date;
                    cell.appendChild(dateDiv);

                    // Check if current day
                    if (date === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                        cell.classList.add('current-day');
                    }

                    // Weekend or regular day
                    if (j === 0 || j === 6) {
                        cell.classList.add('weekend');
                    } else {
                        cell.classList.add('no-class');
                    }

                    // Check attendance for this day
                    if (Array.isArray(attendanceData)) {
                        const dayAttendance = attendanceData.filter(record => {
                            const recordDate = new Date(record.date);
                            return recordDate.getDate() === date && 
                                   recordDate.getMonth() === month && 
                                   recordDate.getFullYear() === year;
                        });
                        
                        if (dayAttendance.length > 0) {
                            cell.classList.remove('no-class', 'weekend');
                            const infoDiv = document.createElement('div');
                            infoDiv.className = 'attendance-info';
                            
                            // Filter attendance based on selected subject
                            const filteredAttendance = selectedSubject === 'all' 
                                ? dayAttendance 
                                : dayAttendance.filter(record => record.subject_title === selectedSubject);
                            
                            if (filteredAttendance.length > 0) {
                                cell.classList.add(filteredAttendance[0].status.toLowerCase());
                                
                                filteredAttendance.forEach(record => {
                                    const statusDiv = document.createElement('div');
                                    statusDiv.className = `attendance-status ${record.status.toLowerCase()}`;
                                    
                                    const shortSubject = record.subject_title
                                        .split(' ')
                                        .map(word => word.charAt(0))
                                        .join('');
                                        
                                    statusDiv.innerHTML = `
                                        <strong>${shortSubject}</strong>
                                        <div>${record.status}</div>
                                    `;
                                    
                                    infoDiv.appendChild(statusDiv);
                                });
                                
                                cell.appendChild(infoDiv);
                            }
                        }
                    }

                    date++;
                }
                row.appendChild(cell);
            }
            calendarBody.appendChild(row);
            if (date > daysInMonth) break;
        }
    }

    async function updateCalendar(subject = selectedSubject) {
        selectedSubject = subject;
        const attendanceData = await fetchAttendanceData(currentMonth + 1, currentYear, selectedSubject);
        console.log('Attendance Data:', attendanceData);
        createCalendar(currentMonth, currentYear, attendanceData);
    }

    // Navigation buttons
    document.getElementById('prevMonth').addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar();
    });

    // Subject filter
    const subjectFilter = document.getElementById('subjectFilter');
    if (subjectFilter) {
        subjectFilter.addEventListener('change', async (e) => {
            selectedSubject = e.target.value;
            console.log('Selected subject:', selectedSubject);
            await updateCalendar(selectedSubject);
        });
    }

    // Initialize calendar
    updateCalendar();

    // Sidebar toggle functionality
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("toggleBtn");
    const calendarContainer = document.querySelector('.calendar-container');

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("hidden");
        calendarContainer.style.marginLeft = sidebar.classList.contains("hidden") ? "0" : "250px";
    });
});

function updateAttendanceTable(attendanceData) {
    const tbody = document.querySelector('.attendance-table tbody');
    tbody.innerHTML = '';

    if (attendanceData.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="4" class="no-data">No attendance records found</td>';
        tbody.appendChild(tr);
        return;
    }

    attendanceData.forEach(record => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${formatDate(record.date)}</td>
            <td>${record.subject_title || 'N/A'}</td>
            <td class="${record.status.toLowerCase()}">${record.status}</td>
            <td>${record.remarks || '-'}</td>
        `;
        tbody.appendChild(tr);
    });
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}