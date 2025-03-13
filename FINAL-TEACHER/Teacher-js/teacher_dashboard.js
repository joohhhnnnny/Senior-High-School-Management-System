document.addEventListener('DOMContentLoaded', function() {
    fetchDashboardData();
    // Refresh data every minute
    setInterval(fetchDashboardData, 60000);
});

function fetchDashboardData() {
    fetch('../Teacher-php/get_dashboard_data.php')
        .then(async response => {
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (!response.ok) {
                    throw new Error(data.error || 'Server error');
                }
                return data;
            } catch (e) {
                console.error('Response text:', text);
                throw new Error('Invalid JSON response');
            }
        })
        .then(data => {
            console.log('Dashboard data:', data); // Debug log
            updateDashboardStats(data);
            updateScheduleDisplay(data.schedules);
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            document.getElementById('todaySchedule').innerHTML = 
                `<div class="error-message">Failed to load schedule data: ${error.message}</div>`;
            // Set stats to 0 on error
            document.getElementById('totalSubjects').textContent = '0';
            document.getElementById('totalStudents').textContent = '0';
        });
}

function updateDashboardStats(data) {
    document.getElementById('totalSubjects').textContent = data.total_subjects || '0';
    document.getElementById('totalStudents').textContent = data.total_students || '0';
}

function updateScheduleDisplay(schedules) {
    const scheduleContainer = document.getElementById('todaySchedule');
    
    if (!schedules || !schedules.length) {
        scheduleContainer.innerHTML = '<div class="no-schedule">No classes scheduled for today</div>';
        return;
    }

    const scheduleHTML = schedules.map(schedule => `
        <div class="schedule-card ${schedule.status}">
            <div class="schedule-time">
                <i class="fas ${getStatusIcon(schedule.status)}"></i>
                ${schedule.formatted_start_time} - ${schedule.formatted_end_time}
            </div>
            <div class="schedule-details">
                <h4>${schedule.subject_name}</h4>
                <p>Section: ${schedule.section_name}</p>
                <p>Room: ${schedule.room}</p>
                <span class="status-badge ${schedule.status}">
                    ${capitalizeFirst(schedule.status)}
                </span>
            </div>
        </div>
    `).join('');

    scheduleContainer.innerHTML = scheduleHTML;
}

function getStatusIcon(status) {
    switch(status) {
        case 'upcoming': return 'fa-clock';
        case 'ongoing': return 'fa-play-circle';
        case 'completed': return 'fa-check-circle';
        default: return 'fa-calendar';
    }
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Handle semester/year changes
document.getElementById('semesterSelect')?.addEventListener('change', fetchDashboardData);
document.getElementById('yearSelect')?.addEventListener('change', fetchDashboardData);
