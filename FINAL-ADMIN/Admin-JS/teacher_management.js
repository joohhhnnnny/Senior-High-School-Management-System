// Add this at the top of the file, before any other code
if (!window.AdminUI) {
    window.AdminUI = {
        AlertSystem: {
            show(message, type = 'success') {
                // Remove any existing alerts
                const existingAlerts = document.querySelectorAll('.custom-alert');
                existingAlerts.forEach(alert => alert.remove());

                // Create new alert
                const alertDiv = document.createElement('div');
                alertDiv.className = `custom-alert ${type}`;
                alertDiv.innerHTML = `
                    <div class="alert-content">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                        <span>${message}</span>
                    </div>
                `;

                // Set styles
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
                    transition: 'all 0.3s ease-in-out'
                });

                document.body.appendChild(alertDiv);

                // Slide down animation
                setTimeout(() => {
                    alertDiv.style.top = '20px';
                }, 100);

                // Slide up and remove after 3 seconds
                setTimeout(() => {
                    alertDiv.style.top = '-100px';
                    setTimeout(() => alertDiv.remove(), 300);
                }, 3000);
            }
        }
    };
}

(function() {
    'use strict';

    const TeacherManagement = {
        // Initialize state
        state: {
            teachers: [],
            filters: {
                search: ''
            }
        },

        // Add utilities section at the top
        utils: {
            debounce(func, wait) {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }
        },

        // Initialize the module
        init() {
            this.loadTeachers();
            this.initializeEventListeners();
            this.initializeSearch();
        },

        // Load teachers data
        async loadTeachers() {
            const tbody = document.getElementById('teacherTableBody');
            if (!tbody) return;

            try {
                tbody.innerHTML = `
                    <tr class="loading-row">
                        <td colspan="5">
                            <div class="loading-indicator">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading teachers...</p>
                            </div>
                        </td>
                    </tr>`;

                const response = await fetch('../Admin-PHP/teacher_management.php');
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers));
                
                // Get raw text first
                const text = await response.text();
                console.log('Raw response:', text.substring(0, 500)); // Log first 500 chars
                
                // Check if response starts with HTML
                if (text.trim().startsWith('<!DOCTYPE')) {
                    throw new Error('Server returned HTML instead of JSON. Check PHP errors.');
                }

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error(`Invalid JSON response: ${text.substring(0, 100)}...`);
                }

                if (!data.success) {
                    throw new Error(data.message || 'Failed to load teachers');
                }

                // Clear the loading message
                tbody.innerHTML = '';
                
                if (!data.data || data.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <p>No teachers found</p>
                                </div>
                            </td>
                        </tr>`;
                    return;
                }

                // Populate the table with teacher data
                data.data.forEach(teacher => {
                    tbody.innerHTML += this.createTeacherRow(teacher);
                });

            } catch (error) {
                console.error('Error loading teachers:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5">
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                Error loading teachers: ${error.message}
                            </div>
                        </td>
                    </tr>`;
            }
        },

        // Initialize event listeners
        initializeEventListeners() {
            // Remove the incorrect direct call
            // openScheduleModal(); <- Remove this line

            // Make sure the schedule button click handler works
            document.addEventListener('click', (e) => {
                const scheduleBtn = e.target.closest('.action-btn.schedule');
                if (scheduleBtn) {
                    const row = scheduleBtn.closest('tr');
                    const id = row.dataset.id;
                    const fullname = row.cells[1].textContent;
                    const professorId = row.cells[0].textContent;
                    
                    console.log('Schedule button clicked:', { id, fullname, professorId });
                    TeacherManagement.openScheduleModal(id, fullname, professorId);
                }
            });

            // Setup close buttons
            document.querySelectorAll('.close, .btn-secondary').forEach(button => {
                button.onclick = (e) => {
                    e.preventDefault();
                    TeacherManagement.closeScheduleModal();
                };
            });

            // Setup search functionality
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('#teacherTableBody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            // Add modal close handlers
            window.closeEditModal = TeacherManagement.closeEditModal;
            window.closeScheduleModal = TeacherManagement.closeScheduleModal;

            // Add click handlers for modal backgrounds
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        TeacherManagement.closeScheduleModal();
                        TeacherManagement.closeEditModal();
                    }
                });
            });

            // Add escape key handler for modals
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    TeacherManagement.closeScheduleModal();
                    TeacherManagement.closeEditModal();
                }
            });

            // Update close button handlers
            document.querySelectorAll('.close').forEach(button => {
                button.onclick = function(e) {
                    e.preventDefault();
                    const modal = this.closest('.modal');
                    if (modal.id === 'scheduleModal') {
                        TeacherManagement.closeScheduleModal();
                    } else if (modal.id === 'editModal') {
                        TeacherManagement.closeEditModal();
                    }
                };
            });

            // Update cancel button handlers
            document.querySelectorAll('.btn-secondary, .cancel-btn').forEach(button => {
                button.onclick = function(e) {
                    e.preventDefault();
                    const modal = this.closest('.modal');
                    if (modal.id === 'scheduleModal') {
                        TeacherManagement.closeScheduleModal();
                    } else if (modal.id === 'editModal') {
                        TeacherManagement.closeEditModal();
                    }
                };
            });

            // Make close functions globally available
            window.closeEditModal = () => TeacherManagement.closeEditModal();
            window.closeScheduleModal = () => TeacherManagement.closeScheduleModal();

            // Add event listeners for subject dropdown population
            const scheduleForm = document.getElementById('assignScheduleForm');
            if (scheduleForm) {
                const strandSelect = scheduleForm.querySelector('#strandSelect');
                const yearLevelSelect = scheduleForm.querySelector('#yearLevelSelect');
                const semesterSelect = scheduleForm.querySelector('#semesterSelect');
        
                const updateSubjectsHandler = () => this.updateSubjects(scheduleForm);
        
                strandSelect?.addEventListener('change', updateSubjectsHandler);
                yearLevelSelect?.addEventListener('change', updateSubjectsHandler);
                semesterSelect?.addEventListener('change', updateSubjectsHandler);
        
                // Add form submit handler
                scheduleForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    await this.handleScheduleSubmit(e);
                });
            }

            // Add edit button click handler
            document.addEventListener('click', (e) => {
                const editBtn = e.target.closest('.action-btn.edit');
                if (editBtn) {
                    const row = editBtn.closest('tr');
                    const id = row.dataset.id;
                    this.editTeacher(id);
                }
            });

            // Add edit form submit handler
            const editForm = document.getElementById('editProfessorForm');
            if (editForm) {
                editForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    await this.saveTeacherChanges(e.target);
                });
            }

            // Remove existing delete button handler
            document.removeEventListener('click', this.handleDeleteClick);
            
            // Add single delete button handler
            document.addEventListener('click', (e) => {
                const deleteBtn = e.target.closest('.action-btn.delete');
                if (deleteBtn) {
                    const row = deleteBtn.closest('tr');
                    const id = row.dataset.id;
                    const name = row.cells[1].textContent;
                    this.deleteTeacher(id, name);
                }
            });

            // Add this in your initializeEventListeners function
            document.getElementById('editProfessorForm')?.addEventListener('submit', async (e) => {
                e.preventDefault();
                await TeacherManagement.saveTeacherChanges(e.target);
            });
        },

        createTeacherRow(teacher) {
            // Add validation and formatting for empty values
            const formatValue = (value) => value && value !== 'Not Set' ? value : '<span class="not-set">Not Set</span>';
            const sanitizeValue = (value) => value ? value.replace(/['"]/g, '&quot;') : '';

            return `
                <tr data-id="${teacher.id}">
                    <td>${teacher.professorID}</td>
                    <td>${formatValue(teacher.fullname)}</td>
                    <td>${formatValue(teacher.email)}</td>
                    <td>${formatValue(teacher.phoneNumber)}</td>
                    <td class="actions">
                        <button class="action-btn schedule" 
                                title="Assign Schedule">
                            <i class="fas fa-calendar-alt"></i>
                        </button>
                        <button class="action-btn edit" 
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete" 
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        },

        async openScheduleModal(id, fullname, professorIdDisplay) {
            console.group('Opening Schedule Modal');
            try {
                const modal = document.getElementById('scheduleModal');
                console.log('Found modal element:', modal);
                console.log('Modal initial style:', modal?.style?.display);
                
                if (!modal) {
                    throw new Error('Schedule modal element not found');
                }

                // Force modal to front
                modal.style.display = 'block';
                modal.style.zIndex = '10000';
                modal.style.opacity = '1';
                modal.style.visibility = 'visible';
                
                // Update fields
                document.getElementById('scheduleProfessorId').value = id; // Store the database ID instead of professorID string
                document.getElementById('scheduleProfessorName').textContent = fullname || 'Not Set';
                document.getElementById('scheduleProfessorIdDisplay').textContent = professorIdDisplay || 'Not Set';

                // Force redraw
                modal.offsetHeight;
                
                console.log('Modal final style:', {
                    display: modal.style.display,
                    zIndex: modal.style.zIndex,
                    opacity: modal.style.opacity,
                    visibility: modal.style.visibility
                });

            } catch (error) {
                console.error('Error in openScheduleModal:', error);
                alert('Error opening schedule modal: ' + error.message);
            }
            console.groupEnd();
        },

        async editTeacher(id) {
            const modal = document.getElementById('editModal');
            
            try {
                if (!modal) {
                    throw new Error('Edit modal element not found in DOM');
                }

                // First show loading state
                modal.style.display = 'block';

                // Get form elements - using correct IDs from HTML
                const elements = {
                    professorIdDisplay: document.getElementById('displayProfessorId'),
                    fullnameInput: document.getElementById('fullname'),
                    emailInput: document.getElementById('email'),
                    phoneInput: document.getElementById('phoneNumber'),
                    editIdInput: document.getElementById('editId')
                };

                // Log which elements were found/not found
                console.log('Form elements found:', Object.entries(elements).reduce((acc, [key, value]) => {
                    acc[key] = !!value;
                    return acc;
                }, {}));

                // Check which elements are missing
                const missingElements = Object.entries(elements)
                    .filter(([_, element]) => !element)
                    .map(([name]) => name);

                if (missingElements.length > 0) {
                    throw new Error(`Missing form elements: ${missingElements.join(', ')}`);
                }

                // Show loading states
                elements.professorIdDisplay.textContent = 'Loading...';
                elements.fullnameInput.value = 'Loading...';
                elements.emailInput.value = 'Loading...';
                elements.phoneInput.value = 'Loading...';

                // Fetch teacher data
                const response = await fetch(`../Admin-PHP/teacher_management_edit.php?id=${id}`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Failed to fetch teacher details');
                }

                // Update form fields
                elements.editIdInput.value = data.data.id;
                elements.professorIdDisplay.textContent = data.data.professorID || 'Not Set';
                elements.fullnameInput.value = data.data.fullname || '';
                elements.emailInput.value = data.data.email || '';
                elements.phoneInput.value = data.data.phoneNumber || '';

                // Show modal with animation
                modal.style.opacity = '1';
                modal.style.visibility = 'visible';

            } catch (error) {
                console.error('Error in editTeacher:', error);
                this.showAlert(error.message, 'error');
                
                // Hide modal if there's an error
                if (modal) {
                    modal.style.display = 'none';
                }
            }
        },

        closeScheduleModal() {
            const modal = document.getElementById('scheduleModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                
                // Reset form if it exists
                const form = document.getElementById('assignScheduleForm');
                if (form) form.reset();
            }
        },

        closeEditModal() {
            console.log('Closing edit modal');
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        },

        setupScheduleForm(form, professorId) {
            // Remove existing listeners
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);

            // Add new submit handler
            newForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleScheduleSubmit(e, professorId);
            });

            // Setup dropdowns
            this.setupDropdowns(newForm);
        },

        setupDropdowns(form) {
            const dropdowns = ['strandSelect', 'yearLevelSelect', 'semesterSelect'];
            dropdowns.forEach(id => {
                const element = form.querySelector(`#${id}`);
                if (element) {
                    element.addEventListener('change', () => this.updateSubjects(form));
                }
            });
        },

        async updateSubjects(form) {
            const subjectSelect = form.querySelector('#subjectSelect');
            const strand = form.querySelector('#strandSelect')?.value?.trim();
            const yearLevel = form.querySelector('#yearLevelSelect')?.value?.trim();
            const semester = form.querySelector('#semesterSelect')?.value?.trim();
            const schoolYear = '2024-2025';
        
            // Clear and disable subject select initially
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            subjectSelect.disabled = true;
        
            // Validate all required fields
            if (!strand || !yearLevel || !semester) {
                console.log('Missing required fields:', { strand, yearLevel, semester });
                TeacherManagement.showAlert('Please select all required fields first', 'warning');
                return;
            }
        
            try {
                // Show loading state
                subjectSelect.innerHTML = '<option value="">Loading subjects...</option>';
        
                // Log parameters being sent
                console.log('Sending parameters:', { strand, yearLevel, semester, schoolYear });
        
                const params = new URLSearchParams({
                    strand: strand,
                    year: yearLevel,
                    semester: semester,
                    school_year: schoolYear
                });
        
                const url = `../Admin-PHP/get_subjects.php?${params.toString()}`;
                console.log('Requesting URL:', url);
        
                const response = await fetch(url);
                const text = await response.text();
        
                // Log raw response for debugging
                console.log('Raw server response:', text);
        
                const data = JSON.parse(text);
                
                if (!data.success) {
                    throw new Error(data.message || 'Server returned an error');
                }
        
                // Reset select with default option
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        
                // Add subjects if available
                if (Array.isArray(data.data) && data.data.length > 0) {
                    data.data.forEach(subject => {
                        const option = new Option(
                            subject.subject_title, // Only display subject_title
                            subject.id
                        );
                        subjectSelect.appendChild(option);
                    });
                    subjectSelect.disabled = false;
                } else {
                    subjectSelect.innerHTML = '<option value="">No subjects found</option>';
                }
        
            } catch (error) {
                console.error('Subject loading error:', error);
                subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                TeacherManagement.showAlert(`Failed to load subjects: ${error.message}`, 'error');
            }
        },

        async handleScheduleSubmit(e) {
            e.preventDefault();
            const submitButton = e.target.querySelector('button[type="submit"]');
        
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
                // Use the numeric ID from the hidden input, not the professorID string
                const formData = {
                    professor_id: parseInt(document.getElementById('scheduleProfessorId').value), // Convert to integer
                    strand: document.getElementById('strandSelect').value,
                    yearLevel: document.getElementById('yearLevelSelect').value,
                    semester: document.getElementById('semesterSelect').value,
                    subject_id: parseInt(document.getElementById('subjectSelect').value), // Also ensure this is numeric
                    day: document.getElementById('daySelect').value,
                    start_time: document.getElementById('timeStart').value,
                    end_time: document.getElementById('timeEnd').value,
                    room: document.getElementById('roomInput').value,
                    school_year: '2024-2025'
                };
        
                // Validate ID is numeric
                if (!Number.isInteger(formData.professor_id)) {
                    throw new Error('Invalid professor ID format');
                }
        
                console.log('Submitting schedule data:', formData);
        
                const response = await fetch('../Admin-PHP/assign_schedule.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
        
                const result = await response.json();
                
                if (result.success) {
                    TeacherManagement.showAlert('Schedule assigned successfully', 'success');
                    TeacherManagement.closeScheduleModal();
                } else {
                    throw new Error(result.message || 'Failed to assign schedule');
                }
        
            } catch (error) {
                console.error('Error:', error);
                TeacherManagement.showAlert(error.message || 'Error assigning schedule', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save"></i> Save Schedule';
            }
        },

        async saveTeacherChanges(form) {
            console.log('Starting saveTeacherChanges...');
            const submitButton = form.querySelector('button[type="submit"]');
            let updateSuccessful = false;
            
            try {
                // Disable submit button and show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                // Get all form elements with debugging
                const formElements = {
                    id: document.getElementById('editId'),
                    fullname: document.getElementById('fullname'),
                    email: document.getElementById('email'),
                    phone: document.getElementById('phoneNumber')
                };

                // Debug log which elements were found
                console.log('Form elements found:', {
                    id: !!formElements.id,
                    fullname: !!formElements.fullname,
                    email: !!formElements.email,
                    phone: !!formElements.phone
                });

                // Validate form elements exist
                const missingElements = Object.entries(formElements)
                    .filter(([_, element]) => !element)
                    .map(([name]) => name);

                if (missingElements.length > 0) {
                    throw new Error(`Missing form elements: ${missingElements.join(', ')}`);
                }

                const formData = {
                    id: formElements.id.value,
                    fullname: formElements.fullname.value.trim(),
                    email: formElements.email.value.trim(),
                    phoneNumber: formElements.phone.value.trim() || null
                };

                // Validate required fields
                if (!formData.fullname || !formData.email) {
                    throw new Error('Name and email are required fields');
                }

                // Validate email format
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
                    throw new Error('Please enter a valid email address');
                }

                // First update attempt
                const response = await fetch('../Admin-PHP/teacher_management_edit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const text = await response.text();
                console.log('Raw response:', text);

                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid server response format');
                }

                if (!result.success) {
                    throw new Error(result.message || 'Failed to update teacher');
                }

                // Mark update as successful
                updateSuccessful = true;

                // Show success message
                window.AdminUI.AlertSystem.show('Teacher details updated successfully!', 'success');
                
                // Close modal first
                this.closeEditModal();

                // Then refresh the table
                try {
                    await this.loadTeachers();
                } catch (refreshError) {
                    console.warn('Table refresh failed:', refreshError);
                    // Don't throw error here since the update was successful
                }

            } catch (error) {
                console.error('Error in saveTeacherChanges:', error);
                window.AdminUI.AlertSystem.show(error.message, 'error');
                
                // If update wasn't successful, keep modal open
                if (!updateSuccessful) {
                    const modal = document.getElementById('editModal');
                    if (modal) {
                        modal.style.display = 'block';
                    }
                }
            } finally {
                // Reset submit button
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                }
            }
        },

        showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `custom-alert ${type}`;
            alertDiv.innerHTML = `
                <div class="alert-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            Object.assign(alertDiv.style, {
                position: 'fixed',
                top: '-100px',
                right: '20px',
                padding: '15px 25px',
                borderRadius: '8px',
                background: type === 'success' ? '#4CAF50' : '#dc3545',
                color: 'white',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                zIndex: '10000',
                transition: 'all 0.3s ease-in-out'
            });

            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.style.top = '20px', 100);
            setTimeout(() => {
                alertDiv.style.top = '-100px';
                setTimeout(() => alertDiv.remove(), 300);
            }, 3000);
        },

        async deleteTeacher(id, name) {
            // Prevent multiple deletion attempts
            if (this.isDeleting) return;
            
            try {
                this.isDeleting = true;
                
                if (!confirm(`Are you sure you want to delete teacher "${name}"? This will also remove all associated schedules.`)) {
                    this.isDeleting = false;
                    return;
                }

                const response = await fetch('../Admin-PHP/teacher_management_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });

                const text = await response.text();
                console.log('Raw server response:', text);

                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    throw new Error(`Server returned invalid JSON response: ${text.substring(0, 100)}...`);
                }

                if (!result.success) {
                    throw new Error(result.message || 'Unknown error occurred');
                }

                // Remove row with animation
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.style.transition = 'all 0.3s ease-out';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        row.remove();
                        // Show single success message
                        window.AdminUI?.AlertSystem.show('Teacher deleted successfully', 'success');
                    }, 300);
                }

            } catch (error) {
                console.error('Delete error:', error);
                window.AdminUI?.AlertSystem.show(error.message || 'Failed to delete teacher', 'error');
            } finally {
                this.isDeleting = false;
            }
        },

        initializeSearch() {
            console.group('Initializing Search Functionality');
            const searchInput = document.getElementById('search');
            
            if (!searchInput) {
                console.error('Search input not found');
                console.groupEnd();
                return;
            }

            // Debounce the search function
            const debouncedSearch = this.utils.debounce((value) => {
                this.handleSearch(value);
            }, 300);

            // Clear previous event listeners
            const newSearchInput = searchInput.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearchInput, searchInput);

            // Add new event listeners
            newSearchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.trim();
                debouncedSearch(searchTerm);
                
                // Add visual feedback
                newSearchInput.classList.toggle('has-value', searchTerm.length > 0);
                
                // Update search icon color
                const searchIcon = newSearchInput.previousElementSibling;
                if (searchIcon) {
                    searchIcon.style.color = searchTerm.length > 0 ? '#003366' : '#666';
                }
            });

            // Add clear button functionality
            const clearButton = document.createElement('button');
            clearButton.className = 'search-clear';
            clearButton.innerHTML = '&times;';
            clearButton.style.display = 'none';
            newSearchInput.parentNode.appendChild(clearButton);

            clearButton.addEventListener('click', () => {
                newSearchInput.value = '';
                clearButton.style.display = 'none';
                this.handleSearch('');
                newSearchInput.focus();
                newSearchInput.classList.remove('has-value');
                const searchIcon = newSearchInput.previousElementSibling;
                if (searchIcon) searchIcon.style.color = '#666';
            });

            // Show/hide clear button based on input
            newSearchInput.addEventListener('input', () => {
                clearButton.style.display = newSearchInput.value.length > 0 ? 'block' : 'none';
            });

            console.log('Search functionality initialized');
            console.groupEnd();
        },

        handleSearch(searchTerm) {
            console.group('Search Operation');
            console.log('Search term:', searchTerm);

            const tbody = document.getElementById('teacherTableBody');
            if (!tbody) {
                console.error('Teacher table body not found');
                console.groupEnd();
                return;
            }

            const rows = tbody.getElementsByTagName('tr');
            let hasVisibleRows = false;
            let visibleCount = 0;
            searchTerm = searchTerm.toLowerCase();

            Array.from(rows).forEach(row => {
                if (row.classList.contains('no-results')) {
                    row.remove();
                    return;
                }

                // Get searchable content
                const professorId = row.cells[0]?.textContent?.toLowerCase() || '';
                const fullName = row.cells[1]?.textContent?.toLowerCase() || '';
                const email = row.cells[2]?.textContent?.toLowerCase() || '';
                const phone = row.cells[3]?.textContent?.toLowerCase() || '';

                // Check if row matches search
                const matchesSearch = !searchTerm || 
                    professorId.includes(searchTerm) || 
                    fullName.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    phone.includes(searchTerm);

                // Add transition for smooth hide/show
                row.style.transition = 'opacity 0.3s ease';
                
                if (matchesSearch) {
                    row.style.display = '';
                    row.style.opacity = '1';
                    hasVisibleRows = true;
                    visibleCount++;
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
                    <td colspan="5" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <p>No teachers found matching "${searchTerm}"</p>
                        </div>
                    </td>
                `;
                tbody.appendChild(noResults);
            }

            // Update search results count if element exists
            const resultsCount = document.getElementById('searchResultsCount');
            if (resultsCount) {
                resultsCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'result' : 'results'} found`;
                resultsCount.style.display = searchTerm ? 'block' : 'none';
            }

            console.log(`Found ${visibleCount} matches`);
            console.groupEnd();
        },

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        showAlert: function(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => alertDiv.remove(), 5000);
        }
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        TeacherManagement.init();
    });

    // Make TeacherManagement globally available
    window.TeacherManagement = TeacherManagement;
})();

// Add this at the top of your script section
(function() {
    console.group('Modal Debug Info');
    
    // Check if modal elements exist
    console.log('Schedule Modal Element:', document.getElementById('scheduleModal'));
    console.log('Edit Modal Element:', document.getElementById('editModal'));
    
    // Check for conflicting styles
    const scheduleModal = document.getElementById('scheduleModal');
    if (scheduleModal) {
        const computedStyle = window.getComputedStyle(scheduleModal);
        console.log('Modal Computed Styles:', {
            display: computedStyle.display,
            position: computedStyle.position,
            zIndex: computedStyle.zIndex,
            opacity: computedStyle.opacity,
            visibility: computedStyle.visibility
        });
    }
    
    // Print all modals in document
    const allModals = document.querySelectorAll('.modal');
    console.log('Total modals found:', allModals.length);
    allModals.forEach((modal, index) => {
        console.log(`Modal ${index}:`, {
            id: modal.id,
            display: modal.style.display,
            zIndex: modal.style.zIndex,
            className: modal.className
        });
    });

    // Check if TeacherManagement is properly initialized
    console.log('TeacherManagement object:', window.TeacherManagement);
    
    console.groupEnd();
})();

// Add submenu toggle functionality
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

// Add search-related styles
document.head.insertAdjacentHTML('beforeend', `
    <style>
        .search-input {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input input {
            width: 100%;
            padding: 12px 40px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-input input:focus {
            border-color: #003366;
            box-shadow: 0 0 0 2px rgba(0, 51, 102, 0.1);
        }

        .search-input i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            transition: color 0.3s ease;
        }

        .search-input input:focus + i {
            color: #003366;
        }

        .search-clear {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            width: 20px;
            height: 20px;
            line-height: 1;
            transition: all 0.3s ease;
        }

        .search-clear:hover {
            color: #003366;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #666;
        }

        .empty-state i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #999;
        }

        #searchResultsCount {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            margin-left: 10px;
        }
    </style>
`);

document.addEventListener('DOMContentLoaded', () => {
    // Initialize TeacherManagement
    TeacherManagement.init();
    
    // Add submenu toggle functionality
    const menuToggles = document.querySelectorAll('.menu-toggle');
    menuToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            const submenu = toggle.nextElementSibling;
            toggle.classList.toggle('active');
            
            if (submenu && submenu.classList.contains('submenu')) {
                if (submenu.classList.contains('show')) {
                    submenu.style.maxHeight = '0px';
                    setTimeout(() => submenu.classList.remove('show'), 300);
                } else {
                    submenu.classList.add('show');
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                }
            }
            
            const arrow = toggle.querySelector('.arrow');
            if (arrow) {
                arrow.style.transform = toggle.classList.contains('active') 
                    ? 'rotate(180deg)' 
                    : 'rotate(0deg)';
            }
        });
    });
});