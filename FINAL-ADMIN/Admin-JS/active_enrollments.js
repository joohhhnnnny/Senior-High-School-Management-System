'use strict';

// Initialize AdminUI if it doesn't exist
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

// Initialize EnrollmentManagement if it doesn't exist
if (!window.EnrollmentManagement) {
    window.EnrollmentManagement = {
        state: {
            originalApplications: [],
            filters: {
                search: '',
                yearLevel: '',
                strand: '',
                status: ''
            }
        },

        init() {
            this.initializeFilters();
            this.loadEnrollments();
            this.initializeEventListeners();
        },

        async loadEnrollments() {
            console.group('Loading Enrollment Data');
            const tbody = document.getElementById('applications-tbody');
            const spinner = document.getElementById('loadingSpinner');
            
            try {
                spinner?.classList.add('show');
                tbody.style.opacity = '0';
                
                const response = await fetch('../Admin-PHP/student_pending_apply.php');
                const data = await response.json();
                
                if (data.success) {
                    this.state.originalApplications = data.data;
                    await this.populateTable(data.data);
                    this.updateStats(data.stats);
                    this.initializeFilters(); // Initialize filters after data load
                } else {
                    throw new Error(data.message || 'Failed to load data');
                }
                
                tbody.style.opacity = '1';
                tbody.classList.add('table-fade-in');
                
            } catch (error) {
                console.error('Error:', error);
                tbody.innerHTML = `<tr><td colspan="7">Error loading data: ${error.message}</td></tr>`;
            } finally {
                spinner?.classList.remove('show');
            }
        },

        initializeFilters() {
            // Remove any existing listeners first
            const searchInput = document.getElementById('search');
            const yearLevel = document.getElementById('year-level');
            const strand = document.getElementById('strand');
            const status = document.getElementById('application-status');

            if (!searchInput || !yearLevel || !strand || !status) {
                console.error('Filter elements not found');
                return;
            }

            // Optimize by removing debounce for filter dropdowns
            yearLevel.addEventListener('change', () => this.filterApplications());
            strand.addEventListener('change', () => this.filterApplications());
            status.addEventListener('change', () => this.filterApplications());

            // Use input event instead of debounced search for immediate feedback
            searchInput.addEventListener('input', () => this.filterApplications());
        },

        filterApplications() {
            const searchTerm = document.getElementById('search')?.value?.toLowerCase() || '';
            const yearLevel = document.getElementById('year-level')?.value || '';
            const strand = document.getElementById('strand')?.value || '';
            const status = document.getElementById('application-status')?.value?.toLowerCase() || '';

            // Create the filter conditions once
            const filters = {
                searchTerm,
                yearLevel,
                strand: strand.toLowerCase(),
                status
            };

            // Use optimized filtering with cached values
            const filteredApplications = this.state.originalApplications.filter(app => {
                if (filters.searchTerm) {
                    const searchableText = `${app.studentID || ''} ${app.fullname || ''}`.toLowerCase();
                    if (!searchableText.includes(filters.searchTerm)) return false;
                }
                
                if (filters.yearLevel && app.yearLevel?.toString() !== filters.yearLevel) return false;
                if (filters.strand && app.strand?.toLowerCase() !== filters.strand) return false;
                if (filters.status && app.status?.toLowerCase() !== filters.status) return false;

                return true;
            });

            requestAnimationFrame(() => {
                this.populateTable(filteredApplications);
                this.updateFilterVisuals();
            });
        },

        // Action Methods
        async approveApplication(id) {
            console.log('Attempting to approve student with ID:', id);
            if (!confirm('Are you sure you want to approve this application?')) {
                return;
            }

            const row = document.querySelector(`tr[data-id="${id}"]`);
            try {
                // Add loading state
                if (row) {
                    row.style.opacity = '0.5';
                    row.style.pointerEvents = 'none';
                }

                const response = await fetch('../Admin-PHP/student_approve_transfer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });

                // Log response status and headers
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers));

                // Get response text first
                const responseText = await response.text();
                console.log('Raw server response:', responseText);

                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse server response:', responseText);
                    throw new Error(`Server returned invalid response: ${responseText.substring(0, 100)}...`);
                }

                // Check for server-side errors
                if (!data.success) {
                    throw new Error(data.message || 'Server returned error response');
                }

                // Success handling
                window.AdminUI.AlertSystem.show('Application approved successfully', 'success');
                await this.loadEnrollments();

            } catch (error) {
                console.error('Approval error:', error);
                window.AdminUI.AlertSystem.show(
                    `Error approving application: ${error.message}`, 
                    'error'
                );
            } finally {
                if (row) {
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'auto';
                }
            }
        },

        async rejectApplication(id) {
            if (!confirm('Are you sure you want to reject this application?')) {
                return;
            }

            const row = document.querySelector(`tr[data-id="${id}"]`);
            try {
                if (row) {
                    row.style.opacity = '0.5';
                    row.style.pointerEvents = 'none';
                }

                const response = await fetch('../Admin-PHP/student_update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        status: 'rejected'
                    })
                });

                const responseText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Invalid JSON response:', responseText);
                    throw new Error('Server returned invalid JSON response');
                }

                if (!data.success) {
                    throw new Error(data.message || 'Failed to reject application');
                }

                // Use window.AdminUI.AlertSystem instead of AlertSystem directly
                window.AdminUI.AlertSystem.show('Application rejected successfully', 'success');
                
                // Update stats if provided
                if (data.stats) {
                    this.updateStats(data.stats);
                }
                
                await this.loadEnrollments();

            } catch (error) {
                console.error('Error:', error);
                window.AdminUI.AlertSystem.show(error.message || 'Error rejecting application', 'error');
            } finally {
                if (row) {
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'auto';
                }
            }
        },

        async deleteApplication(id) {
            console.log('Attempting to delete application:', id);
            
            if (!confirm('Are you sure you want to delete this application?')) {
                return;
            }

            const row = document.querySelector(`tr[data-id="${id}"]`);
            
            try {
                // Add loading state
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0.5';
                }

                const response = await fetch('../Admin-PHP/student_delete_application.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });

                // First get the raw text response and log it
                const responseText = await response.text();
                console.log('Raw server response:', responseText);
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers));

                // Handle non-200 responses
                if (!response.ok) {
                    throw new Error(`Server returned ${response.status}: ${responseText}`);
                }

                // Try to parse JSON only if we have content
                let data;
                if (responseText.trim()) {
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('JSON Parse Error:', parseError);
                        console.error('Invalid response text:', responseText);
                        throw new Error('Server returned invalid JSON response');
                    }
                } else {
                    throw new Error('Server returned empty response');
                }

                if (!data.success) {
                    throw new Error(data.message || 'Failed to delete application');
                }

                // Use the globally scoped AdminUI
                if (window.AdminUI?.AlertSystem?.show) {
                    window.AdminUI.AlertSystem.show('Application deleted successfully', 'success');
                } else {
                    alert('Application deleted successfully');
                }

                // Animate row removal and update table
                if (row) {
                    row.style.transform = 'translateX(-10px)';
                    row.style.opacity = '0';
                    setTimeout(async () => {
                        await this.loadEnrollments();
                        if (data.stats) {
                            this.updateStats(data.stats);
                        }
                    }, 300);
                } else {
                    await this.loadEnrollments();
                }

            } catch (error) {
                console.error('Delete operation error:', error);
                
                if (window.AdminUI?.AlertSystem?.show) {
                    window.AdminUI.AlertSystem.show('Error deleting application: ' + error.message, 'error');
                } else {
                    alert('Error deleting application: ' + error.message);
                }

                // Restore row styling
                if (row) {
                    row.style.opacity = '1';
                    row.style.transform = 'none';
                }
            }
        },

        async viewStudentDetails(id) {
            console.log('Opening view modal for student ID:', id);
            try {
                const modal = document.getElementById('studentModal');
                const modalContent = document.getElementById('studentDetails');
                
                if (!modal || !modalContent) {
                    throw new Error('Modal elements not found');
                }

                modalContent.innerHTML = '<div class="loading">Loading...</div>';
                modal.style.display = 'block';
                
                const response = await fetch(`../Admin-PHP/student_view_details.php?id=${id}`);
                const responseText = await response.text();
                
                try {
                    const data = JSON.parse(responseText);
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to load student details');
                    }

                    const student = data.data;
                    
                    // Fix image paths - use the paths directly from the server response
                    const birthCertPath = student.birthcert || '';
                    const form138Path = student.form138 || '';

                    // Add debug logging
                    console.log('Student Data:', student);
                    console.log('Birth Certificate Path:', birthCertPath);
                    console.log('Form 138 Path:', form138Path);

                    modalContent.innerHTML = `
                        <div class="student-info">
                            <h3>Student Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Full Name:</label>
                                    <span>${student.fullname || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Birth Date:</label>
                                    <span>${student.birthdate || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Gender:</label>
                                    <span>${student.gender?.charAt(0).toUpperCase() + student.gender?.slice(1) || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Address:</label>
                                    <span>${student.address || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Phone Number:</label>
                                    <span>${student.phoneNumber || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Email:</label>
                                    <span>${student.email || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Year Level:</label>
                                    <span>Grade ${student.yearLevel || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <label>Strand:</label>
                                    <span>${student.strand?.toUpperCase() || 'N/A'}</span>
                                </div>
                            </div>

                            <h3 class="documents-header">Required Documents</h3>
                            <div class="documents-grid">
                                <div class="document-item">
                                    <label>Birth Certificate:</label>
                                    ${birthCertPath ? 
                                        `<img src="${birthCertPath}" alt="Birth Certificate" class="document-image" 
                                            onclick="EnrollmentManagement.viewDocument('${birthCertPath}')"
                                            onerror="this.onerror=null; this.src='../Admin-IMAGES/no-image.png'; this.style.opacity=0.5;"
                                        >` : 
                                        '<span class="no-image">No image available</span>'
                                    }
                                </div>
                                <div class="document-item">
                                    <label>Form 138:</label>
                                    ${form138Path ? 
                                        `<img src="${form138Path}" alt="Form 138" class="document-image" 
                                            onclick="EnrollmentManagement.viewDocument('${form138Path}')"
                                            onerror="this.onerror=null; this.src='../Admin-IMAGES/no-image.png'; this.style.opacity=0.5;"
                                        >` : 
                                        '<span class="no-image">No image available</span>'
                                    }
                                </div>
                            </div>
                        </div>
                    `;

                } catch (e) {
                    console.error('Response Text:', responseText);
                    throw new Error(`Failed to parse server response: ${e.message}`);
                }

            } catch (error) {
                console.error('Error:', error);
                modalContent.innerHTML = `<div class="error-message">Error: ${error.message}</div>`;
                window.AdminUI?.AlertSystem.show(error.message, 'error');
            }
        },

        viewDocument(src) {
            const imageModal = document.getElementById('imageModal');
            const expandedImg = document.getElementById('expandedImg');
            const zoomContainer = imageModal.querySelector('.zoom-container');
            
            // Set initial state
            let scale = 1;
            let panning = false;
            let pointX = 0;
            let pointY = 0;
            let start = { x: 0, y: 0 };
            
            // Show image and modal
            expandedImg.src = src;
            imageModal.classList.add('show');
            resetTransform();
            
            function resetTransform() {
                scale = 1;
                pointX = 0;
                pointY = 0;
                expandedImg.style.transform = 'translate(0px, 0px) scale(1)';
            }
            
            // Mouse wheel zoom
            zoomContainer.onwheel = (e) => {
                e.preventDefault();
                const delta = -e.deltaY * 0.01;
                const newScale = Math.min(Math.max(1, scale + delta), 5);
                const rect = expandedImg.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;
                
                if (newScale !== scale) {
                    scale = newScale;
                    expandedImg.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
                }
            };
            
            // Panning functionality
            expandedImg.onmousedown = (e) => {
                e.preventDefault();
                start = {
                    x: e.clientX - pointX,
                    y: e.clientY - pointY
                };
                panning = true;
                expandedImg.style.cursor = 'grabbing';
            };
            
            document.onmousemove = (e) => {
                if (!panning) return;
                e.preventDefault();
                pointX = e.clientX - start.x;
                pointY = e.clientY - start.y;
                expandedImg.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
            };
            
            document.onmouseup = () => {
                panning = false;
                expandedImg.style.cursor = 'grab';
            };
            
            // Control buttons
            const zoomIn = imageModal.querySelector('.zoom-in');
            const zoomOut = imageModal.querySelector('.zoom-out');
            const reset = imageModal.querySelector('.reset');
            const closeBtn = imageModal.querySelector('.close-btn');
            
            zoomIn.onclick = (e) => {
                e.preventDefault();
                if (scale < 5) {
                    scale += 0.5;
                    expandedImg.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
                }
            };
            
            zoomOut.onclick = (e) => {
                e.preventDefault();
                if (scale > 1) {
                    scale -= 0.5;
                    expandedImg.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
                }
            };
            
            reset.onclick = (e) => {
                e.preventDefault();
                resetTransform();
            };
            
            // Double click to reset
            expandedImg.ondblclick = (e) => {
                e.preventDefault();
                resetTransform();
            };
            
            // Close modal
            function closeModal() {
                imageModal.classList.remove('show');
                resetTransform();
                // Clean up event listeners
                document.onmousemove = null;
                document.onmouseup = null;
            }
            
            closeBtn.onclick = (e) => {
                e.preventDefault();
                closeModal();
            };
            
            imageModal.onclick = (e) => {
                if (e.target === imageModal) {
                    closeModal();
                }
            };
        },

        // Helper Methods
        updateStats(stats) {
            if (stats) {
                document.getElementById('pending-count').textContent = stats.pending || '0';
                document.getElementById('approved-count').textContent = stats.approved || '0';
                document.getElementById('total-applications-count').textContent = stats.total || '0';
            }
        },

        updateFilterVisuals() {
            const elements = {
                search: document.getElementById('search'),
                yearLevel: document.getElementById('year-level'),
                strand: document.getElementById('strand'),
                status: document.getElementById('application-status')
            };

            Object.entries(elements).forEach(([key, element]) => {
                if (element) {
                    const hasValue = element.value !== '';
                    element.classList.toggle('active-filter', hasValue);
                }
            });
        },

        async populateTable(applications) {
            const tbody = document.getElementById('applications-tbody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            if (!applications || applications.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No applications found</td></tr>';
                return;
            }
            
            applications.forEach(app => {
                const row = document.createElement('tr');
                const date = new Date(app.date);
                
                console.log('Processing application:', {
                    studentID: app.studentID,
                    fullname: app.fullname,
                    status: app.status,
                    pendingEnrollId: app.id // Log the pending enrollment ID
                });
                
                // Set both IDs as data attributes
                row.setAttribute('data-id', app.studentID);
                row.setAttribute('data-pending-id', app.id);
                
                row.innerHTML = `
                    <td>${app.studentID}</td>
                    <td>${app.fullname || 'N/A'}</td>
                    <td>Grade ${app.yearLevel}</td>
                    <td>${app.strand}</td>
                    <td><span class="status ${app.status}">${app.status}</span></td>
                    <td>${date.toLocaleDateString()} ${date.toLocaleTimeString()}</td>
                    <td class="actions">
                        ${app.status === 'pending' ? `
                            <button class="action-btn approve" onclick="EnrollmentManagement.approveApplication('${app.studentID}')">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="action-btn reject" onclick="EnrollmentManagement.rejectApplication('${app.studentID}')">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        <button class="action-btn view" onclick="EnrollmentManagement.viewStudentDetails('${app.studentID}')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn delete" onclick="EnrollmentManagement.deleteApplication('${app.studentID}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        },

        initializeEventListeners() {
            // Add event listeners for modal close buttons
            const modals = document.querySelectorAll('.student-modal');
            modals.forEach(modal => {
                const closeBtn = modal.querySelector('.close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        modal.style.display = 'none';
                    });
                }
            });

            // Close modal when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target.classList.contains('student-modal')) {
                    event.target.style.display = 'none';
                }
            });
        }
    };
}

// Initialize when DOM is ready (only once)
document.addEventListener('DOMContentLoaded', () => {
    if (window.EnrollmentManagement && !window.EnrollmentManagement.initialized) {
        window.EnrollmentManagement.init();
        window.EnrollmentManagement.initialized = true;
    }
});

// Menu toggle handling (only once)
document.addEventListener('DOMContentLoaded', function() {
    if (!window.menuTogglesInitialized) {
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
        window.menuTogglesInitialized = true;
    }
});