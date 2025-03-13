// Define validation functions first (outside any event listeners)
function validatePage1Fields() {
  const fullName = document.getElementById('fullName').value.trim();
  const birthdate = document.getElementById('birthdate').value.trim();
  const gender = document.getElementById('gender').value;
  const address = document.getElementById('address').value.trim();
  const phone = document.getElementById('phone').value.trim();
  const email = document.getElementById('email').value.trim();

  if (!fullName || !birthdate || !gender || !address || !phone || !email) {
      alert('Please fill in all fields before proceeding.');
      return false;
  }
  
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
      alert('Please enter a valid email address.');
      return false;
  }

  const phonePattern = /^\d{10,11}$/;
  if (!phonePattern.test(phone.replace(/[^0-9]/g, ''))) {
      alert('Please enter a valid phone number (10-11 digits).');
      return false;
  }

  return true;
}

function validatePage2Fields() {
  const yearLevel = document.getElementById('yearLevel').value;
  const strand = document.getElementById('strand').value;
  const birthCert = document.getElementById('birthCertificate').files[0];
  const form138 = document.getElementById('form138').files[0];

  if (!yearLevel || !strand || !birthCert || !form138) {
      alert('Please fill in all fields and upload required documents before proceeding.');
      return false;
  }

  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
  if (!allowedTypes.includes(birthCert.type) || !allowedTypes.includes(form138.type)) {
      alert('Please upload only image files (JPEG, PNG).');
      return false;
  }

  return true;
}

const validatePage3Fields = () => {
  const parentName = document.getElementById('parentfullName').value.trim();
  const parentContact = document.getElementById('parentContact').value.trim();

  if (!parentName || !parentContact) {
      alert('Please fill in all emergency contact fields before submitting.');
      return false;
  }

  const phonePattern = /^\d{10,11}$/;
  if (!phonePattern.test(parentContact.replace(/[^0-9]/g, ''))) {
      alert("Please enter a valid parent's contact number (10-11 digits).");
      return false;
  }

  return true;
};

// Main event listener for DOM content loaded
document.addEventListener('DOMContentLoaded', function () {
const toPage3Btn = document.getElementById('toPage3');
const toPage2Btn = document.getElementById('toPage2');
const toPage1Btn = document.getElementById('toPage1');
const backToPage2Btn = document.getElementById('backToPage2');
const page1 = document.getElementById('page1');
const page2 = document.getElementById('page2');
const page3 = document.getElementById('page3');
const indicator1 = document.getElementById('indicator1');
const indicator2 = document.getElementById('indicator2');
const indicator3 = document.getElementById('indicator3');

// Function to show specific page and update indicators
function showPage(currentPage, nextPage, direction) {
  currentPage.classList.remove('page-active');
  currentPage.classList.add(direction === 'right' ? 'page-prev' : 'page-next');
  nextPage.classList.remove('page-next', 'page-prev');
  nextPage.classList.add('page-active');
  updateIndicator(nextPage === page1 ? 1 : nextPage === page2 ? 2 : nextPage === page3 ? 3 : 0);
}

// Function to update page indicator
function updateIndicator(activePage) {
  indicator1.classList.toggle('active', activePage === 1);
  indicator2.classList.toggle('active', activePage === 2);
  indicator3.classList.toggle('active', activePage === 3);
}

// Event listeners for page navigation buttons
toPage2Btn.addEventListener('click', (e) => {
  e.preventDefault();
  if (validatePage1Fields()) {
    showPage(page1, page2, 'right');
  }
});

toPage1Btn.addEventListener('click', (e) => {
  e.preventDefault();
  showPage(page2, page1, 'left');
});

toPage3Btn.addEventListener('click', (e) => {
  e.preventDefault();
  if (validatePage2Fields()) {
    showPage(page2, page3, 'right');
  }
});

backToPage2Btn.addEventListener('click', (e) => {
  e.preventDefault();
  showPage(page3, page2, 'left');
});

// Initialize the first page and update indicator
page1.classList.add('page-active');
page2.classList.add('page-next');
page3.classList.add('page-next');
updateIndicator(1);

// Event listener for form submission
document.querySelector('form').addEventListener('submit', async function (e) {
  e.preventDefault();
  if (validatePage3Fields()) {
    try {
      const formData = new FormData(this);
      
      const response = await fetch('../client-php/applyStudent.php', {
        method: 'POST',
        body: formData
      });

      // Check if response is OK
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        const result = await response.json();
        if (result.status === 'success') {
          alert(result.message);
          window.location.href = '/CST5-PROJECT/index.php';
        } else {
          throw new Error(result.message);
        }
      } else {
        throw new Error('Server returned non-JSON response');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Error: ' + error.message);
    }
  }
});
});