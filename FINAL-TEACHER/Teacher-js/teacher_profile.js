document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
});

function initializeProfile() {
    loadProfileData();
    setupEventListeners();
}

function updateProfileImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (file.size > maxSize) {
            alert('File size must be less than 5MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImage').src = e.target.result;
            localStorage.setItem('teacherProfileImage', e.target.result);
        };
        reader.onerror = function() {
            alert('Error reading file');
        };
        reader.readAsDataURL(file);
    }
}

function addSubject() {
    const subjectsList = document.getElementById('subjectsList');
    const subjectItem = document.createElement('div');
    subjectItem.className = 'subject-item';
    
    subjectItem.innerHTML = `
        <input type="text" placeholder="Enter subject name" class="subject-input">
        <i class="fas fa-times remove-subject" onclick="removeSubject(this)"></i>
    `;
    
    subjectsList.appendChild(subjectItem);
}

function removeSubject(element) {
    element.parentElement.remove();
}

function saveProfile(event) {
    event.preventDefault();

    const profileData = {
        fullName: document.getElementById('fullName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        department: document.getElementById('department').value,
        subjects: Array.from(document.querySelectorAll('.subject-input'))
            .map(input => input.value)
            .filter(value => value.trim() !== '')
    };

    try {
        // Save to localStorage for demo purposes
        // In real application, this would be an API call
        localStorage.setItem('teacherProfileData', JSON.stringify(profileData));
        showNotification('Profile updated successfully!', 'success');
    } catch (error) {
        console.error('Error saving profile:', error);
        showNotification('Error saving profile', 'error');
    }
}

function loadProfileData() {
    try {
        const savedData = JSON.parse(localStorage.getItem('teacherProfileData')) || {};
        const savedImage = localStorage.getItem('teacherProfileImage');

        if (savedImage) {
            document.getElementById('profileImage').src = savedImage;
        }

        Object.keys(savedData).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = savedData[key];
            }
        });

        // Load subjects
        if (savedData.subjects) {
            const subjectsList = document.getElementById('subjectsList');
            subjectsList.innerHTML = '';
            savedData.subjects.forEach(subject => {
                const subjectItem = document.createElement('div');
                subjectItem.className = 'subject-item';
                subjectItem.innerHTML = `
                    <input type="text" value="${subject}" class="subject-input">
                    <i class="fas fa-times remove-subject" onclick="removeSubject(this)"></i>
                `;
                subjectsList.appendChild(subjectItem);
            });
        }
    } catch (error) {
        console.error('Error loading profile data:', error);
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function setupEventListeners() {
    const form = document.getElementById('profileForm');
    form.addEventListener('submit', saveProfile);
}