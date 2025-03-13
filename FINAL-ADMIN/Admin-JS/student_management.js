(function() {
    'use strict';

    // Initialize state management
    const StudentManagementState = {
        currentStudents: [],
        filters: {
            search: '',
            yearLevel: '',
            strand: ''
        }
    };

    // Add this at the top of the file
    const debug = {
        log: (...args) => {
            if (window.STUDENT_MANAGEMENT_DEBUG) {
                (window.originalConsoleLog || console.log).apply(console, args);
            }
        },
        error: (...args) => {
            if (window.STUDENT_MANAGEMENT_DEBUG) {
                (window.originalConsoleError || console.error).apply(console, args);
            }
        }
    };

    // Add this utility function at the top of the file
    const AdminUtils = {
        debounce: (fn, wait) => {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }
    };

    // Main StudentManagement object with all essential functions
    window.StudentManagement = {
        // Data loading functions
        async loadStudents(silent = false) {
            debug.log('StudentManagement.loadStudents()');
            console.group('StudentManagement.loadStudents()');
            const tbody = document.getElementById('studentTableBody');
            
            if (!tbody) {
                console.error('studentTableBody not found');
                console.groupEnd();
                return;
            }

            try {
                console.log('Fetching students data...');
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Loading...</td></tr>';
                
                const response = await fetch('../Admin-PHP/student_management.php');
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const text = await response.text();
                console.log('Raw response:', text);
                
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load students');
                }

                tbody.innerHTML = '';
                
                if (!data.data || data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center">No students found</td></tr>';
                    return;
                }
                
                data.data.forEach(student => {
                    tbody.innerHTML += `
                        <tr data-id="${student.id}">
                            <td>${student.studentID}</td>
                            <td>${student.fullname}</td>
                            <td>${student.yearLevel}</td>
                            <td>${student.strand}</td>
                            <td>${student.email}</td>
                            <td>${student.address}</td>
                            <td>${student.phoneNumber}</td>
                            <td class="actions">
                                <button class="action-btn view" data-action="view" data-id="${student.id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit" data-action="edit" data-id="${student.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" data-action="delete" data-id="${student.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                // Add event delegation for action buttons
                tbody.addEventListener('click', (e) => {
                    const button = e.target.closest('[data-action]');
                    if (!button) return;

                    const action = button.dataset.action;
                    const id = Number(button.dataset.id);

                    debug.log(`Action ${action} triggered for ID:`, id);

                    switch (action) {
                        case 'view':
                            this.viewStudent(id);
                            break;
                        case 'edit':
                            debug.log("🖊️ Edit button clicked for ID:", id);
                            this.editStudent(id);
                            break;
                        case 'delete':
                            this.deleteStudent(id);
                            break;
                    }
                });

                // Success handling without showing alert if silent is true
                if (!silent && data.success) {
                    this.showAlert('Students loaded successfully', 'success');
                }
                
            } catch (error) {
                console.error('Error:', error);
                if (!silent) {
                    this.showAlert('Error loading students: ' + error.message, 'error');
                }
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            Error loading students: ${error.message}
                        </td>
                    </tr>
                `;
            }
            console.groupEnd();
        },

        // Student CRUD operations
        async viewStudent(id) {
            console.log('Opening view modal for student ID:', id);
            try {
                const modal = document.getElementById('viewModal');
                const closeBtn = modal.querySelector('.close-btn');
                
                // Show loading state
                modal.querySelector('.modal-content').classList.add('loading');
                
                // Set up close button event listener
                closeBtn.onclick = closeViewModal;
                
                modal.classList.add('active');
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                const response = await fetch(`../Admin-PHP/student_management_view.php?id=${id}`);
                console.log('Response received:', response.status);
                const data = await response.json();
                console.log('Data received:', data);
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load student data');
                }

                const { student, grades = [], attendance = [] } = data.data;
                
                console.log('Processing student data:', { student, grades, attendance });
                
                // Update basic student info
                document.getElementById('viewStudentId').textContent = student.studentID || 'N/A';
                document.getElementById('viewFullname').textContent = student.fullname || 'N/A';
                document.getElementById('viewYearLevel').textContent = student.yearLevel ? `Grade ${student.yearLevel}` : 'N/A';
                document.getElementById('viewStrand').textContent = student.strand || 'N/A';

                // Update grades table with empty state handling
                const gradesBody = document.getElementById('viewGradesBody');
                if (grades && grades.length > 0) {
                    gradesBody.innerHTML = grades.map(grade => `
                        <tr class="semester-${grade.semester}">
                            <td>${grade.subject_title}</td>
                            <td>${grade.semester.charAt(0).toUpperCase() + grade.semester.slice(1)}</td>
                            <td>${grade.midterm || 'N/A'}</td>
                            <td>${grade.finals || 'N/A'}</td>
                            <td>${grade.final_grade || 'N/A'}</td>
                            <td><span class="grade-status ${grade.remarks.toLowerCase()}">${grade.remarks}</span></td>
                        </tr>
                    `).join('');
                } else {
                    gradesBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-book"></i>
                                    <p>No grades available</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }

                // Update attendance table with empty state handling
                const attendanceBody = document.getElementById('viewAttendanceBody');
                if (attendance && attendance.length > 0) {
                    attendanceBody.innerHTML = attendance.map(record => `
                        <tr class="semester-${record.semester}">
                            <td>${new Date(record.date).toLocaleDateString()}</td>
                            <td>${record.subject_title}</td>
                            <td><span class="attendance-status ${record.status.toLowerCase()}">${record.status}</span></td>
                            <td>${record.remarks || 'N/A'}</td>
                        </tr>
                    `).join('');
                } else {
                    attendanceBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-calendar"></i>
                                    <p>No attendance records available</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }

                // Set up filters only if there's data
                if (grades.length > 0) setupGradesFilter(grades);
                if (attendance.length > 0) setupAttendanceFilters(attendance);

                // Remove loading state
                modal.querySelector('.modal-content').classList.remove('loading');

            } catch (error) {
                console.error('Error in viewStudent:', error);
                
                // Show error in modal
                const modal = document.getElementById('viewModal');
                modal.querySelector('.modal-content').classList.remove('loading');
                modal.querySelector('.modal-body').innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Error loading student details: ${error.message}</p>
                    </div>
                `;
                
                this.showAlert('Error loading student details: ' + error.message, 'error');
            }
        },

        async editStudent(id) {
            console.group('🔄 EDIT STUDENT FLOW - START');
            try {
                const modal = document.getElementById('editModal');
                const form = document.getElementById('editStudentForm');
                
                if (!modal || !form) {
                    console.error('❌ Missing required elements:', { modal: !!modal, form: !!form });
                    throw new Error('Required modal elements not found');
                }

                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Show loading state
                modal.querySelector('.modal-content').classList.add('loading');
                
                // Fetch and process student data
                const response = await fetch(`../Admin-PHP/student_management_edit.php?id=${id}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load student data');
                }

                const student = data.data;
                console.log('🎯 Processing student data:', student);
                
                // Parse year_strand
                const [yearLevel, strand] = (student.year_strand || '').split('-');
                console.log('📋 Parsed year/strand:', { yearLevel, strand });

                // Update form fields
                console.log('📝 Updating form fields...');
                document.getElementById('editId').value = student.id || '';
                document.getElementById('editStudentId').value = student.studentID || '';
                document.getElementById('editFullname').value = student.fullname || '';
                document.getElementById('editYearLevel').value = yearLevel || '';
                document.getElementById('editStrand').value = strand || '';
                document.getElementById('editEmail').value = student.email || '';
                document.getElementById('editAddress').value = student.address || '';
                document.getElementById('editPhone').value = student.phoneNumber || '';
                console.log('✅ Form fields updated');

                // Show modal and remove loading state
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                modal.querySelector('.modal-content').classList.remove('loading');
                console.log('🔓 Modal displayed');

                // Setup save button handler
                const saveBtn = modal.querySelector('#saveChangesBtn');
                if (saveBtn) {
                    console.log('🔄 Setting up save button handler...');
                    
                    // Remove existing event listeners
                    const newSaveBtn = saveBtn.cloneNode(true);
                    if (saveBtn.parentNode) {
                        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
                    }

                    // Add click handler to the new button
                    newSaveBtn.addEventListener('click', async (e) => {
                        console.group('💾 SAVE BUTTON CLICKED');
                        e.preventDefault();
                        e.stopPropagation();
                        
                        try {
                            console.log('⏳ Starting save process...');
                            newSaveBtn.disabled = true;
                            newSaveBtn.textContent = 'Saving...';

                            // Get form data
                            const formData = {
                                id: document.getElementById('editId').value,
                                fullname: document.getElementById('editFullname').value,
                                address: document.getElementById('editAddress').value,
                                phoneNumber: document.getElementById('editPhone').value
                            };
                            console.log('📤 Form data collected:', formData);

                            // Validate
                            const requiredFields = ['fullname', 'address', 'phoneNumber'];
                            for (const field of requiredFields) {
                                if (!formData[field]) {
                                    throw new Error(`${field} is required`);
                                }
                            }
                            console.log('✅ Validation passed');

                            // Send update request
                            console.log('📡 Sending update request...');
                            const response = await fetch('../Admin-PHP/student_management_edit.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(formData)
                            });

                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const responseText = await response.text();
                            console.log('📥 Raw server response:', responseText);

                            const result = JSON.parse(responseText);
                            console.log('📊 Parsed server response:', result);

                            if (!result.success) {
                                throw new Error(result.message || 'Failed to update student');
                            }

                            // Success handling
                            console.log('✅ Update successful!');
                            modal.style.display = 'none';
                            modal.classList.remove('active');
                            document.body.style.overflow = '';
                            
                            // Show success alert with animation
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'custom-alert success';
                            alertDiv.innerHTML = `
                                <div class="alert-content">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Student updated successfully!</span>
                                </div>
                            `;
                            
                            Object.assign(alertDiv.style, {
                                position: 'fixed',
                                top: '-100px',
                                right: '20px',
                                padding: '15px 25px',
                                borderRadius: '8px',
                                background: '#4CAF50',
                                color: 'white',
                                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                                zIndex: '10000',
                                display: 'flex',
                                alignItems: 'center',
                                gap: '10px',
                                transition: 'all 0.3s ease-in-out',
                                backdropFilter: 'blur(8px)'
                            });

                            document.body.appendChild(alertDiv);

                            // Slide down animation
                            setTimeout(() => alertDiv.style.top = '20px', 100);

                            // Slide up and remove after 3 seconds
                            setTimeout(() => {
                                alertDiv.style.top = '-100px';
                                alertDiv.style.opacity = '0';
                                setTimeout(() => alertDiv.remove(), 300);
                            }, 3000);

                            await this.loadStudents();

                        } catch (error) {
                            console.error('❌ Save error:', error);
                            this.showAlert(error.message, 'error');
                        } finally {
                            newSaveBtn.disabled = false;
                            newSaveBtn.textContent = 'Save Changes';
                            console.groupEnd(); // Save Button Click
                        }
                    });
                } else {
                    console.warn('⚠️ Save button not found, skipping event handler setup');
                }

            } catch (error) {
                console.error('❌ Edit modal error:', error);
                this.showAlert(error.message, 'error');
            }
            console.groupEnd(); // Edit Student Flow
        },

        async deleteStudent(id) {
            console.group('Delete Student');
            try {
                // Single confirmation dialog
                const confirmed = window.confirm('Warning: This will delete all related data (sections, subjects, grades, attendance) for this student. Are you sure you want to proceed?');
                
                if (!confirmed) {
                    console.log('Delete cancelled by user');
                    console.groupEnd();
                    return;
                }

                // Rest of delete logic...
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.classList.add('deleting-row');
                }

                const response = await fetch('../Admin-PHP/student_management_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: Number(id) })
                });

                // ... rest of the code
            } catch (error) {
                console.error('Delete error:', error);
                // ... error handling
            }
            console.groupEnd();
        },

        // UI and Event handling
        initializeEventListeners() {
            console.group('Edit Form Initialization');
            const editForm = document.getElementById('editStudentForm');
            const saveBtn = document.getElementById('saveChangesBtn');
            
            if (!editForm || !saveBtn) {
                console.error('Elements not found:', { editForm: !!editForm, saveBtn: !!saveBtn });
                console.groupEnd();
                return;
            }
        
            // Remove any existing event listeners
            const newSaveBtn = saveBtn.cloneNode(true);
            saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
        
            // Add click handler to save button
            newSaveBtn.addEventListener('click', async (e) => {
                console.group('Save Button Click Handler');
                try {
                    // Log initial state
                    console.log('Save button clicked');
                    
                    // Disable button and show loading state
                    newSaveBtn.disabled = true;
                    newSaveBtn.textContent = 'Saving...';
                    
                    const formData = {
                        id: document.getElementById('editId').value,
                        fullname: document.getElementById('editFullname').value,
                        email: document.getElementById('editEmail').value,
                        address: document.getElementById('editAddress').value,
                        phoneNumber: document.getElementById('editPhone').value
                    };
                    
                    console.log('Form data prepared:', formData);
                    
                    console.log('Sending request to:', '../Admin-PHP/student_management_edit.php');
                    const response = await fetch('../Admin-PHP/student_management_edit.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    console.log('Response received:', response.status, response.statusText);
                    const responseText = await response.text();
                    console.log('Raw response:', responseText);
                    
                    let result;
                    try {
                        result = JSON.parse(responseText);
                        console.log('Parsed response:', result);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        throw new Error('Invalid server response');
                    }
                    
                    if (result.success) {
                        console.log('Update successful');
                        this.showAlert('Student updated successfully', 'success');
                        closeEditModal();
                        await this.loadStudents();
                    } else {
                        throw new Error(result.message || 'Failed to update student');
                    }
                } catch (error) {
                    console.error('Error in save handler:', error);
                    this.showAlert(error.message, 'error');
                } finally {
                    console.log('Resetting button state');
                    newSaveBtn.disabled = false;
                    newSaveBtn.textContent = 'Save Changes';
                    console.groupEnd();
                }
            });
        
            console.log('Event listeners initialized');
            console.groupEnd();
        },

        initializeFilters() {
            console.group('Initializing Filters');
            
            const searchInput = document.querySelector('#search');
            const yearLevelSelect = document.querySelector('#year-level');
            const strandSelect = document.querySelector('#strand');
            const tbody = document.querySelector('#studentTableBody');
        
            if (!searchInput || !yearLevelSelect || !strandSelect || !tbody) {
                console.error('Missing filter elements');
                console.groupEnd();
                return;
            }
        
            const filterTable = () => {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const yearLevel = yearLevelSelect.value.trim();
                const strand = strandSelect.value.trim();
                
                const rows = Array.from(tbody.getElementsByTagName('tr'));
                let hasVisibleRows = false;
        
                // Remove existing no-results row if it exists
                const existingNoResults = tbody.querySelector('.no-results');
                if (existingNoResults) {
                    existingNoResults.remove();
                }
        
                rows.forEach(row => {
                    if (!row.cells || row.cells.length === 0) return;
        
                    const cells = Array.from(row.cells);
                    if (cells.length < 4) return; // Skip if row doesn't have enough cells
        
                    // Get specific cell values
                    const studentData = {
                        id: cells[0]?.textContent?.toLowerCase() || '',
                        name: cells[1]?.textContent?.toLowerCase() || '',
                        yearLevel: cells[2]?.textContent?.toLowerCase() || '',
                        strand: cells[3]?.textContent?.toLowerCase() || '',
                        email: cells[4]?.textContent?.toLowerCase() || '',
                        address: cells[5]?.textContent?.toLowerCase() || '',
                        phone: cells[6]?.textContent?.toLowerCase() || ''
                    };
        
                    // Match conditions
                    const matchesSearch = !searchTerm || Object.values(studentData).some(value => value.includes(searchTerm));
                    const matchesYear = !yearLevel || studentData.yearLevel.includes(yearLevel.toLowerCase());
                    const matchesStrand = !strand || studentData.strand.includes(strand.toLowerCase());
        
                    const isVisible = matchesSearch && matchesYear && matchesStrand;
        
                    if (isVisible) {
                        row.style.display = '';
                        row.style.opacity = '1';
                        hasVisibleRows = true;
                    } else {
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.style.display = 'none';
                        }, 300);
                    }
                });
        
                // Show no results message if needed
                if (!hasVisibleRows) {
                    const noResults = document.createElement('tr');
                    noResults.classList.add('no-results');
                    noResults.innerHTML = `
                        <td colspan="8" class="text-center">
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <p>No matching records found</p>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(noResults);
                }
        
                // Update visual feedback
                searchInput.classList.toggle('active-filter', searchTerm !== '');
                yearLevelSelect.classList.toggle('active-filter', yearLevel !== '');
                strandSelect.classList.toggle('active-filter', strand !== '');
            };
        
            // Debounce the search for better performance
            const debouncedFilter = AdminUtils.debounce(filterTable, 300);
        
            // Add event listeners
            searchInput.addEventListener('input', debouncedFilter);
            yearLevelSelect.addEventListener('change', filterTable);
            strandSelect.addEventListener('change', filterTable);
        
            // Search icon behavior
            const searchIcon = searchInput.previousElementSibling;
            searchInput.addEventListener('focus', () => {
                searchIcon.style.opacity = '0';
                searchIcon.style.transform = 'translateX(-10px)';
            });
        
            searchInput.addEventListener('blur', () => {
                if (!searchInput.value) {
                    searchIcon.style.opacity = '1';
                    searchIcon.style.transform = 'translateX(0)';
                }
            });
        
            // Initial filter
            filterTable();
        
            console.log('Filter initialization complete');
            console.groupEnd();
        },

        // Alert System
        showAlert(message, type = 'success') {
            // Remove any existing alerts
            const existingAlerts = document.querySelectorAll('.custom-alert');
            existingAlerts.forEach(alert => alert.remove());
        
            const alertDiv = document.createElement('div');
            alertDiv.className = `custom-alert ${type}`;
            alertDiv.innerHTML = `
                <div class="alert-content">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
        
            Object.assign(alertDiv.style, {
                position: 'fixed',
                top: '-100px',
                right: '20px',
                padding: '15px 25px',
                borderRadius: '8px',
                background: type === 'success' ? '#4CAF50' : '#f44336',
                color: 'white',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                zIndex: '10000',
                display: 'flex',
                alignItems: 'center',
                gap: '10px',
                transition: 'all 0.3s ease-in-out',
                backdropFilter: 'blur(8px)'
            });
        
            document.body.appendChild(alertDiv);
        
            // Slide down animation
            requestAnimationFrame(() => {
                alertDiv.style.top = '20px';
                alertDiv.style.opacity = '1';
            });
        
            // Slide up and remove after 3 seconds
            setTimeout(() => {
                alertDiv.style.top = '-100px';
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 3000);
        },

        // Initialize styles
        initializeStyles() {
            const style = document.createElement('style');
            style.textContent = `
                .custom-alert {
                    animation: slideDown 0.3s forwards;
                }
                @keyframes slideDown {
                    from { top: -100px; opacity: 0; }
                    to { top: 20px; opacity: 1; }
                }

                @keyframes slideUp {
                    from { top: 20px; opacity: 1; }
                    to { top: -100px; opacity: 0; }
                }

                .deleting-row {
                    animation: fadeOutRow 0.3s ease forwards;
                }
                @keyframes fadeOutRow {
                    to {
                        opacity: 0;
                        transform: translateX(-100%);
                        height: 0;
                        padding: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    };

    // Add these new helper functions
    function setupGradesFilter(grades) {
        const filter = document.getElementById('gradesFilter');
        const semesters = [...new Set(grades.map(g => g.semester))].sort();
        
        // Reset filter options
        filter.innerHTML = `
            <option value="all">All Semesters</option>
            ${semesters.map(sem => `
                <option value="${sem}">${sem.charAt(0).toUpperCase() + sem.slice(1)} Semester</option>
            `).join('')}
        `;

        filter.onchange = () => {
            const semester = filter.value;
            const rows = document.querySelectorAll('#viewGradesBody tr');
            let hasVisibleRows = false;
            
            rows.forEach(row => {
                if (semester === 'all' || row.classList.contains(`semester-${semester}`)) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show "No data" message if no rows are visible
            const tbody = document.getElementById('viewGradesBody');
            if (!hasVisibleRows) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>No grades found for this semester</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        };
    }

    function setupAttendanceFilters(attendance) {
        const semesterFilter = document.getElementById('attendanceSemesterFilter');
        const subjectFilter = document.getElementById('attendanceSubjectFilter');
        
        // Get unique semesters and subjects
        const semesters = [...new Set(attendance.map(a => a.semester))].sort();
        const subjects = [...new Set(attendance.map(a => a.subject_title))].sort();
        
        // Populate semester filter
        semesterFilter.innerHTML = `
            <option value="all">All Semesters</option>
            ${semesters.map(sem => `
                <option value="${sem}">${sem.charAt(0).toUpperCase() + sem.slice(1)} Semester</option>
            `).join('')}
        `;

        // Populate subject filter
        subjectFilter.innerHTML = `
            <option value="all">All Subjects</option>
            ${subjects.map(subject => `
                <option value="${subject}">${subject}</option>
            `).join('')}
        `;

        // Filter function with empty state handling
        const filterAttendance = () => {
            const semester = semesterFilter.value;
            const subject = subjectFilter.value;
            const rows = document.querySelectorAll('#viewAttendanceBody tr');
            let hasVisibleRows = false;
            
            rows.forEach(row => {
                const showSemester = semester === 'all' || row.classList.contains(`semester-${semester}`);
                const showSubject = subject === 'all' || row.cells[1].textContent === subject;
                if (showSemester && showSubject) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show "No data" message if no rows are visible
            const tbody = document.getElementById('viewAttendanceBody');
            if (!hasVisibleRows) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>No attendance records found</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        };

        // Add event listeners
        semesterFilter.onchange = filterAttendance;
        subjectFilter.onchange = filterAttendance;
    }

    // Essential global modal handlers
    window.closeViewModal = function() {
        const modal = document.getElementById('viewModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    // Update the closeEditModal function
    window.closeEditModal = function() {
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    window.closeStudentModal = function() {
        const modal = document.getElementById('studentModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        if (window.StudentManagement) {
            window.StudentManagement.loadStudents().then(() => {
                window.StudentManagement.initializeFilters();
                window.StudentManagement.initializeStyles();
                window.StudentManagement.initializeEventListeners();
            });
        }
    });

    // Also initialize immediately if DOM is already loaded
    if (document.readyState !== 'loading') {
        if (window.StudentManagement) {
            window.StudentManagement.loadStudents().then(() => {
                window.StudentManagement.initializeFilters();
                window.StudentManagement.initializeStyles();
                window.StudentManagement.initializeEventListeners();
            });
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggles = document.querySelectorAll('.menu-toggle');
        
        menuToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const submenu = toggle.nextElementSibling;
                
                // Toggle active class on the menu toggle
                toggle.classList.toggle('active');
                
                // Toggle show class on submenu
                if (submenu && submenu.classList.contains('submenu')) {
                    if (submenu.classList.contains('show')) {
                        submenu.style.maxHeight = '0px';
                        setTimeout(() => {
                            submenu.classList.remove('show');
                        }, 300);
                    } else {
                        submenu.classList.add('show');
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    }
                }
                
                // Rotate arrow icon
                const arrow = toggle.querySelector('.arrow');
                if (arrow) {
                    arrow.style.transform = toggle.classList.contains('active') 
                        ? 'rotate(180deg)' 
                        : 'rotate(0deg)';
                }
            });
        });
    });
})();

// Ensure these functions are globally available
window.viewStudentDetails = function(id) {
    if (window.StudentManagement) {
        window.StudentManagement.viewStudent(id);
    }
};

window.editStudentDetails = function(id) {
    if (window.StudentManagement) {
        window.StudentManagement.editStudent(id);
    }
};

// window.deleteStudent = function(id) {
//     if (window.StudentManagement) {
//         window.StudentManagement.deleteStudent(id);
//     }
// };

// Add Alert System to window
window.showAlert = function(message, type) {
    if (window.StudentManagement) {
        window.StudentManagement.showAlert(message, type);
    }
};

// Add this at the end of the file
console.log('📚 Student Management JS Loaded');