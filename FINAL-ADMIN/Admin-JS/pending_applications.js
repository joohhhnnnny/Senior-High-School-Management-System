'use strict';

const PendingApplications = {
    state: {
        applications: [],
        filters: {
            search: '',
            status: ''
        }
    },

    init() {
        console.log('Initializing Pending Applications');
        this.loadApplications();
        this.initializeFilters();
        this.setupEventListeners();
    },

    async loadApplications() {
        console.group('Loading Professor Applications');
    
        try {
            const url = '../Admin-PHP/professor_pending_apply.php';
            console.log('Fetching from:', url);
            
            // Log the full request
            console.log('Request details:', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const response = await fetch(url);
            console.log('Response received:', {
                status: response.status,
                statusText: response.statusText,
                headers: Object.fromEntries(response.headers)
            });

            const text = await response.text();
            console.log('Raw response text:', text.substring(0, 1000));

            let data;
            try {
                data = JSON.parse(text);
                console.log('Parsed data:', data.data);
            } catch (e) {
                console.error('Parse error:', e);
                throw new Error('Failed to parse response');
            }

            if (data.success) {
                // Normalize the data to ensure status is always set
                this.state.applications = data.data.map(app => ({
                    ...app,
                    status: app.status || 'pending' // Set default status
                }));
                
                // Calculate initial stats when data is first loaded
                const initialStats = {
                    pending: this.state.applications.filter(app => !app.status || app.status === 'pending').length,
                    approved: this.state.applications.filter(app => app.status === 'approved').length,
                    rejected: this.state.applications.filter(app => app.status === 'rejected').length,
                    total: this.state.applications.length
                };

                // Update display with initial stats
                this.updateDisplay({
                    success: true,
                    data: this.state.applications,
                    stats: initialStats
                });
            } else {
                throw new Error(data.message || 'Server returned error status');
            }

        } catch (error) {
            console.error('Error in loadProfessorApplications:', error);
            this.showAlert(error.message, 'error');
        }
        
        console.groupEnd();
    },

    initializeFilters() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        let searchTimeout;

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.filterApplications(), 300);
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterApplications());
        }
    },

    filterApplications() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const searchTerm = searchInput?.value?.toLowerCase() || '';
        const statusValue = statusFilter?.value?.toLowerCase() || '';

        console.log('Filtering with:', { searchTerm, statusValue });

        // Filter the applications array first
        const filteredApplications = this.state.applications.filter(app => {
            const emailMatch = app.email?.toLowerCase().includes(searchTerm);
            const idMatch = String(app.id).toLowerCase().includes(searchTerm);
            const statusMatch = !statusValue || (app.status || 'pending').toLowerCase() === statusValue;
            
            return (emailMatch || idMatch) && statusMatch;
        });

        // Update table with filtered results
        const tbody = document.getElementById('applicationsTableBody');
        if (tbody) {
            if (filteredApplications.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">No matching applications found</td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = filteredApplications.map(app => {
                    const statusClass = (app.status || 'pending').toLowerCase();
                    const statusText = app.status ? 
                        app.status.charAt(0).toUpperCase() + app.status.slice(1).toLowerCase() : 
                        'Pending';
                    
                    return `
                        <tr data-id="${app.id}">
                            <td>PROF-${String(app.id).padStart(3, '0')}</td>
                            <td>
                                <div class="applicant-info">
                                    <span class="applicant-name">ID: ${app.professorID}</span>
                                    <span class="applicant-email">${app.email}</span>
                                </div>
                            </td>
                            <td>Teaching Position</td>
                            <td>${new Date(app.date).toLocaleDateString()}</td>
                            <td><span class="status ${statusClass}">${statusText}</span></td>
                            <td class="actions">
                                <button class="action-btn view" onclick="handleAction('view', ${app.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${!app.status || app.status === 'pending' ? `
                                    <button class="action-btn approve" onclick="handleAction('approve', ${app.id})">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-btn reject" onclick="handleAction('reject', ${app.id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : ''}
                                <button class="action-btn delete" onclick="handleAction('delete', ${app.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        }

        // Update stats for filtered results
        const filteredStats = {
            pending: filteredApplications.filter(app => !app.status || app.status === 'pending').length,
            approved: filteredApplications.filter(app => app.status === 'approved').length,
            rejected: filteredApplications.filter(app => app.status === 'rejected').length,
            total: filteredApplications.length
        };

        // Update display with filtered data and stats
        this.updateDisplay({ data: filteredApplications, stats: filteredStats });
    },

    async handleAction(action, id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        console.log(`Handling ${action} for ID: ${id}`);
        
        try {
            // For view action, handle differently
            if (action === 'view') {
                const response = await fetch(`../Admin-PHP/pending_applications_view.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });
                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load application details');
                }

                // Show modal with application details
                const modal = document.getElementById('professorModal');
                const details = document.getElementById('professorDetails');
                if (modal && details) {
                    details.innerHTML = `
                        <div class="detail-row">
                            <label>Professor ID:</label>
                            <span>${data.data.professorID || 'Not Set'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Full Name:</label>
                            <span>${data.data.fullname || 'Not Set'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Email:</label>
                            <span>${data.data.email || 'Not Set'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Phone:</label>
                            <span>${data.data.phoneNumber || 'Not Set'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Address:</label>
                            <span>${data.data.address || 'Not Set'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Status:</label>
                            <span class="status ${data.data.status?.toLowerCase()}">${data.data.status || 'Not Set'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Date Applied:</label>
                            <span>${new Date(data.data.date).toLocaleDateString() || 'Not Set'}</span>
                        </div>
                    `;

                    modal.style.display = 'block';

                    // Add close button functionality
                    const closeBtn = modal.querySelector('.close');
                    if (closeBtn) {
                        closeBtn.onclick = () => modal.style.display = 'none';
                    }

                    // Close modal when clicking outside
                    window.onclick = (event) => {
                        if (event.target === modal) {
                            modal.style.display = 'none';
                        }
                    };
                }
                return;
            }
            // Check for HTML error page in response
            let endpoint = '../Admin-PHP/pending_applications_' + action + '.php';

            // Enhanced debug logging
            console.log('Request details:', {
                endpoint,
                id,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id })
            });

            // Show loading state
            if (row) {
                row.classList.add('updating');
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id })
            });

            // Log response details
            console.log('Response status:', response.status);
            console.log('Response headers:', Object.fromEntries(response.headers));
            const responseText = await response.text();
            console.log('Raw response:', responseText);

            // Check for HTML error page in response
            if (responseText.includes('<!DOCTYPE html>')) {
                throw new Error('Server returned HTML instead of JSON. Possible PHP error.');
            }

            let data;
            try {
                // Take only the first valid JSON object from response
                const firstJsonObject = responseText.match(/\{[^}]+\}/)[0];
                data = JSON.parse(firstJsonObject);
                console.log('Parsed response:', data);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Raw response text:', responseText);
                throw new Error(`Invalid JSON response: ${responseText.substring(0, 100)}...`);
            }

            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }

            if (data.success) {
                // Update row immediately
                if (row && action !== 'delete') {
                    // Format status text properly
                    const statusText = action === 'approve' ? 'Approved' : 
                                     action === 'reject' ? 'Rejected' : 
                                     action.charAt(0).toUpperCase() + action.slice(1);
                    
                    const statusClass = action === 'approve' ? 'approved' : 
                                      action === 'reject' ? 'rejected' : 
                                      action.toLowerCase();
                    
                    const statusCell = row.querySelector('td:nth-child(5)');
                    if (statusCell) {
                        statusCell.innerHTML = `<span class="status ${statusClass}">${statusText}</span>`;
                    }
                    
                    // Update actions
                    const actionsCell = row.querySelector('td:last-child');
                    if (actionsCell) {
                        actionsCell.innerHTML = `
                            <button class="action-btn view" onclick="handleAction('view', ${id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn delete" onclick="handleAction('delete', ${id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    }
                }
                // Show success message
                this.showAlert(
                    data.message || `Professor application ${action}ed successfully`, 
                    'success'
                );

                // Refresh the data
                await this.loadApplications();
            } else {
                throw new Error(data.message || `Failed to ${action} application`);
            }

        } catch (error) {
            console.error(`Error in ${action}:`, error);
            console.error('Full error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            this.showAlert(
                `Error: ${error.message}`, 
                'error'
            );
        } finally {
            if (row && action !== 'delete') {
                row.classList.remove('updating');
            }
        }
    },

    updateDisplay(data) {
        console.group('Updating Display');
    
        // Always calculate and show stats
        const stats = data.stats || this.calculateStats(data.data || this.state.applications);

        // Update stats display
        const statsContainer = document.getElementById('statsContainer');
        if (statsContainer) {
            console.log('Updating stats:', stats);
            statsContainer.innerHTML = `
                <div class="stat-card">
                    <i class="fas fa-hourglass-half"></i>
                    <div class="stat-info">
                        <h3>Pending Applications</h3>
                        <span>${stats.pending || 0}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-info">
                        <h3>Approved Applications</h3>
                        <span>${stats.approved || 0}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-times-circle"></i>
                    <div class="stat-info">
                        <h3>Rejected Applications</h3>
                        <span>${stats.rejected || 0}</span>
                    </div>
                </div>
            `;
        }

        // Update table with status colors
        const tbody = document.getElementById('applicationsTableBody');
        if (tbody && data.data) {
            console.log('Updating table with', data.data.length, 'records');
            tbody.innerHTML = data.data.map(app => {
                // Ensure status is properly formatted
                const statusClass = app.status.toLowerCase();
                const statusText = app.status.charAt(0).toUpperCase() + app.status.slice(1).toLowerCase();
                
                return `
                    <tr data-id="${app.id}">
                        <td>PROF-${String(app.id).padStart(3, '0')}</td>
                        <td>
                            <div class="applicant-info">
                                <span class="applicant-name">ID: ${app.professorID}</span>
                                <span class="applicant-email">${app.email}</span>
                            </div>
                        </td>
                        <td>Teaching Position</td>
                        <td>${new Date(app.date).toLocaleDateString()}</td>
                        <td><span class="status ${statusClass}">${statusText}</span></td>
                        <td class="actions">
                            <button class="action-btn view" onclick="handleAction('view', ${app.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${app.status === 'pending' ? `
                                <button class="action-btn approve" onclick="handleAction('approve', ${app.id})">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="action-btn reject" onclick="handleAction('reject', ${app.id})">
                                    <i class="fas fa-times"></i>
                                </button>
                            ` : ''}
                            <button class="action-btn delete" onclick="handleAction('delete', ${app.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        console.groupEnd();
    },

    showAlert(message, type) {
        // Create custom alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert ${type}`;
        alertDiv.innerHTML = `
            <div class="alert-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        // Style the alert
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

    calculateStats(data) {
        return {
            pending: data.filter(app => !app.status || app.status === 'pending').length,
            approved: data.filter(app => app.status === 'approved').length,
            rejected: data.filter(app => app.status === 'rejected').length,
            total: data.length
        };
    },

    setupEventListeners() {
        // Setup submenu toggle functionality
        const menuToggles = document.querySelectorAll('.menu-toggle');
        menuToggles.forEach(toggle => {
            const submenu = toggle.nextElementSibling;
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                // Toggle active class on the menu toggle
                toggle.classList.toggle('active');
                // Toggle show class on submenu
                if (submenu && submenu.classList.contains('submenu')) {
                    if (submenu.classList.contains('show')) {
                        submenu.classList.remove('show');
                        submenu.style.maxHeight = '0px';
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
    }
};

// Make functions globally available if needed
window.handleAction = function(action, id) {
    PendingApplications.handleAction(action, id);
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    PendingApplications.init();
});