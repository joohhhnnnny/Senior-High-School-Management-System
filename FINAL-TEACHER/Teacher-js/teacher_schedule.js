let currentGradeLevel = '11';

document.addEventListener('DOMContentLoaded', function() {
    fetchSchedule(currentGradeLevel);
    
    document.getElementById('gradeLevel')?.addEventListener('change', function(e) {
        currentGradeLevel = e.target.value;
        fetchSchedule(currentGradeLevel);
    });

    document.querySelectorAll('.class-card').forEach(card => {
        card.addEventListener('mouseenter', function(e) {
            const rect = this.getBoundingClientRect();
            this.style.setProperty('--mouse-x', rect.left + 'px');
            this.style.setProperty('--mouse-y', rect.top + 'px');
        });
    });
});

function fetchSchedule(yearLevel) {
    const url = `../Teacher-php/get_teacher_schedule.php?year_level=${yearLevel}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Log the raw response for debugging
            response.clone().text().then(t => console.log('Raw response:', t));
            return response.text();
        })
        .then(text => {
            if (!text) {
                throw new Error('Empty response received');
            }
            // Try to clean the response if it contains HTML
            const cleanText = text.replace(/<[^>]*>/g, '').trim();
            try {
                return JSON.parse(cleanText);
            } catch (e) {
                console.error('JSON Parse Error:', e);
                console.log('Cleaned text:', cleanText);
                throw new Error('Invalid JSON response');
            }
        })
        .then(data => {
            if (!data) {
                throw new Error('No data received');
            }
            if (data.error) {
                console.error('Server error:', data.error, data.debug || '');
                throw new Error(data.error);
            }
            if (data.success) {
                if (data.schedule) renderSchedule(data.schedule);
                if (data.yearLevels) updateGradeLevelSelect(data.yearLevels);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            const classesGrid = document.querySelector('.classes-grid');
            if (classesGrid) {
                classesGrid.innerHTML = `
                    <div class="error-message">
                        Failed to load schedule: ${error.message}
                        <br>
                        Please check the console for more details.
                    </div>`;
            }
        });
}

function updateGradeLevelSelect(yearLevels) {
    const select = document.getElementById('gradeLevel');
    select.innerHTML = '';
    
    yearLevels.forEach(level => {
        const option = document.createElement('option');
        option.value = level;
        option.textContent = `Grade ${level}`;
        select.appendChild(option);
    });
    
    // Set current selection
    select.value = currentGradeLevel;
}

function renderSchedule(scheduleData) {
    const classesGrid = document.querySelector('.classes-grid');
    classesGrid.innerHTML = ''; // Clear existing content

    // Create a map of unique subjects
    const uniqueSubjects = new Set();
    scheduleData.forEach(schedule => uniqueSubjects.add(schedule.subject_title));
    const subjectArray = Array.from(uniqueSubjects);

    const dayMap = {
        'Monday': 1,
        'Tuesday': 2,
        'Wednesday': 3,
        'Thursday': 4,
        'Friday': 5
    };

    scheduleData.forEach(schedule => {
        const startTime = new Date(`1970-01-01T${schedule.start_time}`);
        const gridRow = getTimeSlotRow(startTime);
        const gridColumn = dayMap[schedule.day_of_week];

        // Get subject index for color coding
        const subjectIndex = subjectArray.indexOf(schedule.subject_title) % 5;

        const classSlot = document.createElement('div');
        classSlot.className = 'class-slot';
        classSlot.style.gridRow = gridRow;
        classSlot.style.gridColumn = gridColumn;

        classSlot.innerHTML = `
            <div class="class-card" data-subject-index="${subjectIndex}">
                <h4>${schedule.subject_title}</h4>
                <p>${schedule.section_name}</p>
                <p>Room ${schedule.room}</p>
                <p>${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}</p>
            </div>
        `;

        classesGrid.appendChild(classSlot);
    });
}

function getTimeSlotRow(time) {
    const hours = time.getHours();
    const minutes = time.getMinutes();
    const timeValue = hours + minutes/60;
    
    if (timeValue >= 7 && timeValue < 9) return 1;
    if (timeValue >= 9 && timeValue < 11) return 2;
    if (timeValue >= 11 && timeValue < 13) return 3;
    if (timeValue >= 13 && timeValue < 15) return 4;
    if (timeValue >= 15 && timeValue < 17) return 5;
    if (timeValue >= 17 && timeValue < 19) return 6;
    return 1;
}

function formatTime(timeString) {
    const time = new Date(`1970-01-01T${timeString}`);
    return time.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}