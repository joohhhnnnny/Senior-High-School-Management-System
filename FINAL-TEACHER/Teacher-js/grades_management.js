document.addEventListener('DOMContentLoaded', setupGradesPage);

function setupGradesPage() {
    setupEventListeners();
    initializeFilters();
}

function setupEventListeners() {
    const subjectFilter = document.getElementById('subjectFilter');
    if (subjectFilter) {
        subjectFilter.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                updateSectionDisplay(selectedOption.dataset.sectionId);
                loadGrades(); // Automatically load grades when subject changes
            }
        });
    }

    // Add event listeners for grade inputs
    document.addEventListener('input', function(e) {
        if (e.target.matches('.written-work, .performance-task, .exam-grade')) {
            calculateGrade(e.target.closest('tr'));
        }
    });
}

async function initializeFilters() {
    try {
        const response = await fetch('../Teacher-php/get_teacher_subjects.php');
        const data = await response.json();
        
        console.log('Raw API response:', data);
        
        if (!data.success) {
            throw new Error(data.message);
        }

        // Get the section input element
        const sectionInput = document.getElementById('sectionInput');
        
        // Set section information immediately if sections exist
        if (data.sections && data.sections.length > 0) {
            const firstSection = data.sections[0];
            sectionInput.value = firstSection.section_name;
            sectionInput.dataset.sectionId = firstSection.id;
            console.log('Setting initial section:', firstSection.section_name);
        }

        // Populate subject dropdown
        const subjectSelect = document.getElementById('subjectFilter');
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';

        if (data.subjects && data.subjects.length > 0) {
            // Create a map to store unique section names
            const sectionMap = new Map();
            data.sections.forEach(section => {
                sectionMap.set(section.id, section.section_name);
            });

            // Group subjects by section
            const groupedSubjects = {};
            data.subjects.forEach(subject => {
                const sectionName = sectionMap.get(parseInt(subject.section_id));
                if (!groupedSubjects[sectionName]) {
                    groupedSubjects[sectionName] = [];
                }
                groupedSubjects[sectionName].push(subject);
            });

            // Create optgroups and options
            Object.entries(groupedSubjects).forEach(([sectionName, subjects]) => {
                const optgroup = document.createElement('optgroup');
                optgroup.label = sectionName;

                subjects.forEach(subject => {
                    const option = document.createElement('option');
                    option.value = subject.id;
                    option.dataset.sectionId = subject.section_id;
                    option.dataset.semester = subject.semester;
                    option.dataset.subjectTitle = subject.subject_title;
                    
                    const scheduleInfo = subject.schedule ? 
                        `${subject.schedule.day} ${subject.schedule.time} ${subject.schedule.room ? `(${subject.schedule.room})` : ''}` : 
                        'TBA';
                    
                    option.textContent = `${subject.subject_title} (${scheduleInfo})`;
                    optgroup.appendChild(option);
                });

                subjectSelect.appendChild(optgroup);
            });

            // Select the first subject by default and trigger change event
            if (subjectSelect.options.length > 1) {
                subjectSelect.selectedIndex = 1;
                const event = new Event('change');
                subjectSelect.dispatchEvent(event);
            }
        }

    } catch (error) {
        console.error('Error in initializeFilters:', error);
        showNotification('Error loading subjects', 'error');
    }
}

function updateSectionDisplay(sectionId) {
    const sectionInput = document.getElementById('sectionInput');
    const subjectSelect = document.getElementById('subjectFilter');
    const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];

    // Get the section name from the optgroup label
    if (selectedOption && selectedOption.parentElement.tagName === 'OPTGROUP') {
        const sectionName = selectedOption.parentElement.label;
        sectionInput.value = sectionName;
        sectionInput.dataset.sectionId = sectionId;
        
        // Add debug logging
        console.log('Updating section display:', {
            sectionId: sectionId,
            sectionName: sectionName,
            selectedOption: selectedOption.outerHTML
        });
    } else {
        sectionInput.value = '';
        sectionInput.dataset.sectionId = '';
    }
}

async function loadGrades() {
    const sectionInput = document.getElementById('sectionInput');
    const subjectSelect = document.getElementById('subjectFilter');
    
    if (!subjectSelect.value) {
        return;
    }

    showLoading(true);
    try {
        const params = {
            section_id: sectionInput.dataset.sectionId,
            subject_id: subjectSelect.value
        };
        
        console.log('Loading grades for:', params);

        const response = await fetch(`../Teacher-php/get_student_grades.php?${new URLSearchParams(params)}`);
        const data = await response.json();
        
        console.log('Received grades data:', data);

        if (!data.success) {
            if (data.message === 'Not authenticated') {
                // Redirect to login page if session expired
                window.location.href = '../../FINAL-ADMIN/Portal-Main/main.php';
                return;
            }
            throw new Error(data.message);
        }

        populateGradesTable(data.data);
    } catch (error) {
        console.error('Error loading grades:', error);
        showNotification('Error loading grades: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

function populateGradesTable(students) {
    const tbody = document.getElementById('gradesTableBody');
    tbody.innerHTML = '';

    if (!students || students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No students found</td></tr>';
        return;
    }

    students.forEach(student => {
        const row = document.createElement('tr');
        row.dataset.yearLevel = student.yearLevel; // Store yearLevel in row dataset
        row.innerHTML = `
            <td>${student.student_id}</td>
            <td>${student.fullname}</td>
            <td>
                <input type="number" 
                       class="midterm" 
                       value="${student.midterm || ''}" 
                       min="0" 
                       max="100" 
                       step="0.01"
                       ${student.midterm ? 'data-original="' + student.midterm + '"' : ''}>
            </td>
            <td>
                <input type="number" 
                       class="finals" 
                       value="${student.finals || ''}" 
                       min="0" 
                       max="100" 
                       step="0.01"
                       ${student.finals ? 'data-original="' + student.finals + '"' : ''}>
            </td>
            <td class="final-grade">${student.final_grade || 'N/A'}</td>
            <td class="remarks ${(student.remarks || 'incomplete').toLowerCase()}">${student.remarks || 'Incomplete'}</td>
            <td>
                <button onclick="saveGrades(this)" class="save-btn">
                    <i class="fas fa-save"></i> Save
                </button>
            </td>
        `;

        // Add event listeners for inputs
        const inputs = row.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                const row = input.closest('tr');
                calculateFinalGrade(row);
                
                // Add visual indicator for changed values
                const original = input.dataset.original;
                if (original) {
                    input.classList.toggle('changed', input.value !== original);
                }
            });
        });

        tbody.appendChild(row);
    });
}

function calculateFinalGrade(row) {
    const midterm = parseFloat(row.querySelector('.midterm').value) || 0;
    const finals = parseFloat(row.querySelector('.finals').value) || 0;
    
    if (midterm === 0 && finals === 0) {
        row.querySelector('.final-grade').textContent = 'N/A';
        updateRemarks(row, 'Incomplete');
        return;
    }

    const finalGrade = (midterm + finals) / 2;
    row.querySelector('.final-grade').textContent = finalGrade.toFixed(2);
    
    // Update remarks based on final grade
    if (!midterm || !finals) {
        updateRemarks(row, 'Incomplete');
    } else if (finalGrade >= 75) {
        updateRemarks(row, 'Passed');
    } else {
        updateRemarks(row, 'Failed');
    }
}

function updateRemarks(row, status) {
    const remarksCell = row.querySelector('.remarks');
    remarksCell.textContent = status;
    remarksCell.className = `remarks ${status.toLowerCase()}`;
}

function calculateGrade(row) {
    const written = parseFloat(row.querySelector('.written-work').value) || 0;
    const performance = parseFloat(row.querySelector('.performance-task').value) || 0;
    const exam = parseFloat(row.querySelector('.exam-grade').value) || 0;

    const finalGrade = (written * 0.3) + (performance * 0.5) + (exam * 0.2);
    row.querySelector('.final-grade').textContent = finalGrade.toFixed(2);
    row.querySelector('.remarks').textContent = finalGrade >= 75 ? 'Passed' : 'Failed';
    row.querySelector('.remarks').className = `remarks ${finalGrade >= 75 ? 'passed' : 'failed'}`;
}

function getCurrentSemester() {
    const now = new Date();
    const month = now.getMonth() + 1; // JavaScript months are 0-based
    
    // First semester: August to December
    // Second semester: January to May
    if (month >= 8 && month <= 12) {
        return 'first';
    } else {
        return 'second';
    }
}

async function saveGrades(btn) {
    const row = btn.closest('tr');
    const subjectSelect = document.getElementById('subjectFilter');
    const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
    
    // Add detailed logging
    console.log('Selected subject details:', {
        value: selectedOption.value,
        semester: selectedOption.dataset.semester,
        subjectTitle: selectedOption.dataset.subjectTitle,
        rawDataset: {...selectedOption.dataset},
        optionHTML: selectedOption.outerHTML
    });

    if (!selectedOption) {
        showNotification('Please select a subject first', 'error');
        return;
    }

    // Get values from the row
    const studentId = row.cells[0].textContent;
    const midterm = row.querySelector('.midterm')?.value || null;
    const finals = row.querySelector('.finals')?.value || null;
    const finalGrade = row.querySelector('.final-grade')?.textContent;
    const remarks = row.querySelector('.remarks')?.textContent;
    const yearLevel = row.dataset.yearLevel;

    const gradeData = {
        studentId: studentId,
        subject_title: selectedOption.dataset.subjectTitle,
        semester: selectedOption.dataset.semester, // Make sure this is coming from the dataset
        yearLevel: yearLevel,
        midterm: midterm,
        finals: finals,
        final_grade: finalGrade !== 'N/A' ? finalGrade : null,
        remarks: remarks
    };

    // Add pre-request logging
    console.log('Grade data being sent:', gradeData);

    // Validate required fields
    if (!studentId || (!midterm && !finals)) {
        showNotification('Please enter at least one grade', 'error');
        return;
    }

    try {
        const response = await fetch('../Teacher-php/save_grades.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(gradeData)
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification('Grades saved successfully!', 'success');
            // Update the original values to reflect the saved state
            const midtermInput = row.querySelector('.midterm');
            const finalsInput = row.querySelector('.finals');
            if (midtermInput) {
                midtermInput.dataset.original = midterm;
                midtermInput.classList.remove('changed');
            }
            if (finalsInput) {
                finalsInput.dataset.original = finals;
                finalsInput.classList.remove('changed');
            }
        } else {
            throw new Error(result.message || 'Failed to save grades');
        }
    } catch (error) {
        console.error('Error saving grades:', error);
        showNotification('Error saving grades: ' + error.message, 'error');
    }
}

function saveAllGrades() {
    const allRows = document.querySelectorAll('#gradesTableBody tr');
    const gradesData = Array.from(allRows).map(row => ({
        studentId: row.querySelector('.student-id').textContent,
        writtenWork: row.querySelector('.written-work').value,
        performanceTask: row.querySelector('.performance-task').value,
        examGrade: row.querySelector('.exam-grade').value,
        finalGrade: row.querySelector('.final-grade').textContent
    }));

    showLoading(true);
    // Simulated API call - replace with actual API
    setTimeout(() => {
        console.log('Saving all grades:', gradesData);
        showNotification('All grades saved successfully!', 'success');
        showLoading(false);
    }, 1000);
}

function exportGrades() {
    const classSection = document.getElementById('classFilter').value;
    const subject = document.getElementById('subjectFilter').value;
    
    // Implementation for Excel export
    console.log(`Exporting grades for ${classSection} - ${subject}`);
    showNotification('Grades exported successfully!', 'success');
}

// Helper Functions
function showLoading(show) {
    document.getElementById('loadingSpinner').style.display = show ? 'flex' : 'none';
}

function showNotification(message, type) {
    // Implementation for showing notifications
    alert(message);
}

function handleError(error) {
    console.error('Error:', error);
    showNotification('An error occurred. Please try again.', 'error');
}

// Add more helper functions as needed