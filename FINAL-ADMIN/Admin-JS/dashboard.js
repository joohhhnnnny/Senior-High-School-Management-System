'use strict';

async function updateDashboardStats() {
    try {
        const response = await fetch('../Admin-PHP/dashboard.php');
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        console.log('Raw Response:', response);
        
        // Read response as text first
        const text = await response.text();
        console.log('Response Text:', text);
        
        // Try to parse the text as JSON
        const data = JSON.parse(text);
        
        if (!data.success) throw new Error(data.message);
        
        // Update stats with animation
        animateCounter('studentCount', data.data.students);
        animateCounter('teacherCount', data.data.teachers);
    } catch (error) {
        console.error('Error updating dashboard stats:', error);
    }
}

function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const duration = 1000; // Animation duration in ms
    const start = parseInt(element.textContent) || 0;
    const increment = (targetValue - start) / (duration / 16);
    let current = start;
    
    const animate = () => {
        current += increment;
        if (
            (increment > 0 && current >= targetValue) || 
            (increment < 0 && current <= targetValue)
        ) {
            element.textContent = targetValue;
            return;
        }
        
        element.textContent = Math.round(current);
        requestAnimationFrame(animate);
    };
    
    requestAnimationFrame(animate);
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    updateDashboardStats();
    // Update stats every 30 seconds
    setInterval(updateDashboardStats, 30000);
});

// Add sidebar menu toggle functionality
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