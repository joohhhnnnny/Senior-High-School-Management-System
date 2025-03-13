'use strict';

class ScheduleDisplay {
    constructor() {
        console.log('ScheduleDisplay initialized');
        this.timeSlots = this.generateTimeSlots();
        this.currentStrand = 'HUMSS';
        this.currentSemester = 'first';
        this.initializeEventListeners();
        this.setDefaultStrand();

        // Add debug logging
        console.log('Initial strand:', this.currentStrand);
    }

    // Modified setDefaultStrand method
    setDefaultStrand() {
        const strandFilter = document.getElementById('strandFilter');
        if (strandFilter) {
            strandFilter.value = 'HUMSS';
            this.currentStrand = 'HUMSS';
            this.loadSchedules(); // Call loadSchedules after setting the strand
        }
    }

    generateTimeSlots() {
        const slots = [];
        for (let hour = 7; hour <= 17; hour += 2) {
            const startTime = `${hour.toString().padStart(2, '0')}:00`;
            const endTime = `${(hour + 2).toString().padStart(2, '0')}:00`;
            const period = hour < 12 ? 'AM' : 'PM';
            const nextPeriod = (hour + 2) <= 12 ? 'AM' : 'PM';
            const formattedSlot = `${hour}:00${period} - ${(hour + 2)}:00${nextPeriod}`;
            slots.push({ display: formattedSlot, value: startTime });
        }
        return slots;
    }

    initializeEventListeners() {
        const strandFilter = document.getElementById('strandFilter');
        if (strandFilter) {
            strandFilter.addEventListener('change', (e) => {
                console.log('Strand changed to:', e.target.value);
                this.currentStrand = e.target.value;
                this.loadSchedules();
            });
        }
    }

    async loadSchedules() {
        console.log('Loading schedules for strand:', this.currentStrand);
        if (!this.currentStrand) return;

        try {
            const firstSemesterResponse = await fetch(`../Admin-PHP/get_grade11_schedules.php?strand=${this.currentStrand}&semester=first`);
            const secondSemesterResponse = await fetch(`../Admin-PHP/get_grade11_schedules.php?strand=${this.currentStrand}&semester=second`);
            
            const firstSemesterData = await firstSemesterResponse.json();
            console.log('First semester data:', firstSemesterData);
            const secondSemesterData = await secondSemesterResponse.json();
            console.log('Second semester data:', secondSemesterData);

            if (firstSemesterData.success) {
                this.renderSchedules(firstSemesterData.schedules, 'firstSemesterTable');
            }
            if (secondSemesterData.success) {
                this.renderSchedules(secondSemesterData.schedules, 'secondSemesterTable');
            }
        } catch (error) {
            console.error('Error loading schedules:', error);
        }
    }

    renderSchedules(schedules, tableId) {
        console.log(`Rendering schedules for table ${tableId}:`, schedules);
        const table = document.getElementById(tableId);
        if (!table) {
            console.error(`Table with id ${tableId} not found`);
            return;
        }

        this.clearSchedules(table);
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        schedules.forEach(schedule => {
            console.log('Processing schedule:', schedule);
            const startTime = schedule.start_time.substring(0, 5);
            const dayIndex = days.indexOf(schedule.day);
            
            if (dayIndex !== -1) {
                const cell = this.findScheduleCell(startTime, schedule.day, table);
                if (cell) {
                    console.log('Found cell for:', startTime, schedule.day);
                    cell.innerHTML = this.createScheduleSlotHTML(schedule);
                    cell.classList.add('has-schedule');
                } else {
                    console.log('No cell found for:', startTime, schedule.day);
                }
            }
        });
    }

    findScheduleCell(time, day, table) {
        const tableBody = table.querySelector('tbody');
        const rows = tableBody.querySelectorAll('tr');
        const scheduleHour = parseInt(time.split(':')[0]);
        
        for (let row of rows) {
            const timeCell = row.querySelector('.time-column');
            const timeText = timeCell.textContent;
            const startHour = parseInt(timeText.split(':')[0]);
            const endHour = startHour + 2;
            
            // Check if schedule falls within this time slot
            if (scheduleHour >= startHour && scheduleHour < endHour) {
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                const dayIndex = days.indexOf(day);
                const cells = row.querySelectorAll('td');
                return cells[dayIndex + 1];
            }
        }
        return null;
    }

    createScheduleSlotHTML(schedule) {
        const subjectType = schedule.subject_type?.toLowerCase() || 'unknown';
        const startTime = schedule.start_time.substring(0, 5);
        const endTime = schedule.end_time.substring(0, 5);
        const startHour = parseInt(startTime.split(':')[0]);
        const endHour = parseInt(endTime.split(':')[0]);
        const startPeriod = startHour < 12 ? 'AM' : 'PM';
        const endPeriod = endHour < 12 ? 'AM' : 'PM';
        
        // Simplified subject type class mapping
        const subjectClass = `${subjectType}-subject`;
        
        return `
            <div class="schedule-slot ${subjectClass}">
                <span class="subject-name">${schedule.subject_name || 'Unknown Subject'}</span>
                <span class="professor-name">${schedule.professor_name || 'TBA'}</span>
                <span class="room-info">Room ${schedule.room || 'TBA'}</span>
                <div class="time-display">
                    <span>${startTime}${startPeriod}</span>
                    <span>to</span>
                    <span>${endTime}${endPeriod}</span>
                </div>
            </div>
        `;
    }

    clearSchedules(table) {
        const cells = table.querySelectorAll('td:not(.time-column)');
        cells.forEach(cell => {
            cell.innerHTML = '';
            cell.classList.remove('has-schedule');
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing ScheduleDisplay');
    const scheduleDisplay = new ScheduleDisplay();
    window.scheduleDisplay = scheduleDisplay; // Make it accessible for debugging

    // Generate time slots for both tables
    generateTimeSlots('firstSemesterTable');
    generateTimeSlots('secondSemesterTable');

    // Set default strand in dropdown
    const strandFilter = document.getElementById('strandFilter');
    if (strandFilter) {
        strandFilter.value = 'HUMSS';
    }

    // Generate time slots for both tables
    function generateTimeSlots(tableId) {
        const tbody = document.querySelector(`#${tableId} tbody`);
        for (let hour = 7; hour <= 17; hour += 2) {
            const period = hour < 12 ? 'AM' : 'PM';
            const nextHour = hour + 2;
            const nextPeriod = nextHour <= 12 ? 'AM' : 'PM';
            
            const row = document.createElement('tr');
            
            // Add time column with vertical format
            const timeCell = document.createElement('td');
            timeCell.className = 'time-column';
            timeCell.innerHTML = `
                <div class="time-column-content">
                    <div class="time-start">${hour}:00${period}</div>
                    <div class="time-separator">to</div>
                    <div class="time-end">${nextHour}:00${nextPeriod}</div>
                </div>
            `;
            row.appendChild(timeCell);
            
            // Add empty cells for each day
            for (let i = 0; i < 5; i++) {
                const cell = document.createElement('td');
                row.appendChild(cell);
            }
            
            tbody.appendChild(row);
        }
    }
});