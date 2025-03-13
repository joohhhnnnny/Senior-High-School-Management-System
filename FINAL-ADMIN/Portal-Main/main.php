<?php
// session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISCP All-around Portal</title>
  <style>
    @keyframes smoothGradientMovement {
  0% { background-position: 0% 0%; }
  50% { background-position: 100% 0%; }
  100% { background-position: 0% 0%; }
}

@keyframes gradientText {
  0% {
    background-position: 0% 50%;
    text-shadow: 0 0 3px rgba(0, 153, 255, 0.2);
  }
  50% {
    background-position: 100% 50%;
    text-shadow: 0 0 6px rgba(0, 153, 255, 0.3);
  }
  100% {
    background-position: 0% 50%;
    text-shadow: 0 0 3px rgba(0, 153, 255, 0.2);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes flipHorizontal {
  0% { transform: rotateY(0deg); }
  100% { transform: rotateY(360deg); }
}

    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }
    
    @keyframes slideOutLeft {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(-100%); opacity: 0; }
    }

    @keyframes slideInRight {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideInLeft {
      from { transform: translateX(-100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      height: 100vh;
      position: relative; /* Add this to ensure absolute positioning works */
    }

    .home-container {
      background-color: rgba(255, 255, 255, 0.9);
      padding: 40px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      width: 100%;
      max-width: 500px;
      text-align: center;
      margin-right: 50px; /* Add some spacing from the right edge */
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: fadeInUp 0.8s ease-out;
      position: relative;
      overflow: hidden;
      min-height: 330px; /* Add minimum height to prevent shrinking */
      z-index: 1000;
      pointer-events: all;
      transform: translateZ(0);  /* Force GPU acceleration */
    }

    .header-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      margin-bottom: 10px;
    }

    .logo {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
    }

    .subheader {
      color: #666;
      margin-top: -20px;
      margin-bottom: 30px;
      font-size: 1.1em;
      font-style: italic;
      animation: fadeIn 1.2s ease-out;
    }

    h1 {
      background: linear-gradient(90deg, #003366, #0099ff, #003366);
      background-size: 200% auto;
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: gradientText 15s linear infinite;
      margin-bottom: 30px;
    }

    .home-option {
      border: none;
      width: 100%;
      display: block;
      background: linear-gradient(90deg, #003366, #0099ff, #003366);
      background-size: 200% auto;
      color: white;
      padding: 15px;
      margin: 10px 0;
      font-size: 18px;
      text-decoration: none;
      border-radius: 5px;
      transition: all 0.3s ease;
      animation: smoothGradientMovement 10s linear infinite;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
      cursor: pointer;
      z-index: 3;
      transform: translateZ(0);  /* Force GPU acceleration */
      will-change: transform;    /* Optimize animations */
      backface-visibility: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease, background-position 0.3s ease;
    }

    .home-option:hover {
      transform: translateY(-3px);
      background-position: right center;
      box-shadow: 0 6px 20px rgba(0, 153, 255, 0.2);
    }

    .home-option::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: rgba(255, 255, 255, 0.1);
      transform: rotate(45deg);
      transition: 0.5s;
      pointer-events: none;
    }

    .home-option:hover::after {
      left: 100%;
    }

    footer {
      position: absolute;
      font-weight: bold;
      font-style: italic;
      bottom: 2px;
      width: 100%;
      text-align: center;
      font-size: 15px;
      color:#0099ff; /* Changed to dark color for white background */
      background: linear-gradient(90deg, transparent, rgba(0, 153, 255, 0.1), transparent);
      padding: 10px 0;
      animation: fadeIn 1.5s ease-out;
    }

    .welcome-container {
      position: absolute;
      left: 100px;
      top: 47%;
      transform: translateY(-50%);
      text-align: center; /* Center the logo */
      animation: fadeIn 1s ease-out;
      z-index: 1;
    }

    .welcome-logo {
      width: 150px;  /* Changed from 100px */
      height: 150px; /* Changed from 100px */
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 30px; /* Increased from 20px for better spacing */
      box-shadow: 0 0 20px rgba(0, 153, 255, 0.3);
      animation: flipHorizontal 6s linear infinite; /* Removed float animation, restored faster flip */
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

    .welcome-text {
      font-size: 3em;
      margin: 0;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .welcome-text span {
      color: black;
    }

    .welcome-text .highlight {
      color: #0099ff;
      font-weight: bold;
    }

    .tagline {
      color: #666;
      font-size: 1.5em;
      margin-top: 10px;
      font-weight: normal;
      animation: fadeInUp 1s ease-out;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    .description {
      color: #666;
      font-size: 1.2em;
      margin-top: 5px;
      font-style: italic;
      opacity: 0.8;
      animation: fadeInUp 1s ease-out;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    .content-wrapper {
      position: relative;
      width: 100%;
      height: 100%;
      transition: transform 0.4s ease-in-out, opacity 0.4s ease-in-out;
      transform: translateX(0);
      opacity: 1;
      z-index: 2;
      pointer-events: auto;
    }

    .content-wrapper.slide-out {
      transform: translateX(-100%);
      opacity: 0;
    }

    .login-form {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: white;
      transform: translateX(100%);
      transition: transform 0.4s ease-in-out;
      opacity: 0;
      z-index: 4;
      display: none;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 20px;
      box-sizing: border-box;
      gap: 15px; /* Add consistent spacing between elements */
    }

    .login-form.active {
      transform: translateX(0);
      opacity: 1;
      display: flex;
    }

    .login-form h2 {
      color: #003366;
      margin-bottom: 30px;
      font-size: 24px;
    }

    .login-form form {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      max-width: 320px;
      gap: 15px;
      margin-bottom: 15px; /* Reduced from 25px */
    }

    .login-form input {
      width: 100%;
      padding: 12px;
      margin: 5px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
      box-sizing: border-box;
      transition: border-color 0.3s ease;
    }

    .login-form input:focus {
      border-color: #0099ff;
      outline: none;
    }

    .login-form button[type="submit"] {
      width: 100%;
      padding: 15px;
      border: none;
      border-radius: 5px;
      background: linear-gradient(90deg, #003366, #0099ff, #003366);
      background-size: 200% auto;
      color: white;
      font-size: 18px;
      cursor: pointer;
      transition: all 0.3s ease;
      animation: smoothGradientMovement 10s linear infinite;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      margin-top: 10px;
    }

    .login-form button[type="submit"]:hover {
      background-position: right center;
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 153, 255, 0.2);
    }

    .back-button {
      width: auto;
      min-width: 200px;
      max-width: 320px;
      padding: 12px 25px;
      margin-top: 10px; /* Reduced from 20px */
      font-size: 16px;
      background: linear-gradient(90deg, #003366, #0099ff, #003366);
      background-size: 200% auto;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
      animation: smoothGradientMovement 10s linear infinite;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .back-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 153, 255, 0.2);
      background-position: right center;
    }

    .slide-out {
      transform: translateX(-100%);
      opacity: 0;
    }

    .slide-in {
      animation: none;
    }

    /* Unified button styles - applies to all buttons */
    .home-option, .login-form button[type="submit"], .back-button {
        border: none;
        background: linear-gradient(90deg, #003366, #0099ff, #003366);
        background-size: 200% auto;
        color: white;
        padding: 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        animation: smoothGradientMovement 15s linear infinite;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
        transform: translateZ(0);
        will-change: transform;
        backface-visibility: hidden;
    }

    /* Unified hover effects */
    .home-option:hover, .login-form button[type="submit"]:hover, .back-button:hover {
        transform: translateY(-3px);
        background-position: right center;
        box-shadow: 0 6px 20px rgba(21, 47, 65, 0.2);
        filter: brightness(0.9);
    }

    /* Unified sliding diagonal highlight */
    .home-option::after, .login-form button[type="submit"]::after, .back-button::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(45deg);
        transition: 0.5s;
        pointer-events: none;
    }

    .home-option:hover::after, .login-form button[type="submit"]:hover::after, .back-button:hover::after {
        left: 100%;
    }

    /* Specific adjustments for login form buttons */
    .login-form button[type="submit"] {
        width: 100%;
        margin-top: 10px;
        font-size: 18px;
    }

    .back-button {
        width: auto;
        min-width: 200px;
        max-width: 400px;
        margin-top: 0px;
        font-size: 16px;
    }

    .custom-alert {
        position: fixed;
        top: -100px;
        left: 50%;
        transform: translateX(-50%);
        padding: 20px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        transition: all 0.5s ease-in-out;
        text-align: center;
        min-width: 300px;
        background: #0099ff;  /* Solid blue color */
        border: none;
    }

    .custom-alert.error {
        background: #ff3333;  /* Solid red color */
    }

    .custom-alert.success {
        background: #00cc66;  /* Solid green color */
    }

    .custom-alert.show {
        top: 20px;
    }

    .custom-alert .message {
        color: white;
        font-size: 16px;
        margin: 0;
        font-weight: 500;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }

    .back-to-landing {
        position: absolute;
        bottom: 15px;
        left: 15px;
        color: #0099ff;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        opacity: 1;
        z-index: 2;
    }

    .back-to-landing:hover {
        color: #003366;
    }

    /* Add this to handle visibility during transitions */
    .content-wrapper.slide-out ~ .back-to-landing {
        opacity: 0;
        transform: translateX(-20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .login-form.active ~ .back-to-landing {
        opacity: 0;
        pointer-events: none;
    }
  </style>
</head>

<body>
  <div class="welcome-container">
    <img src="../Images/iscp-logo.png" alt="ISCP Logo" class="welcome-logo">
    <h1 class="welcome-text">
      <span>Welcome to</span><br>
      <span class="highlight">ISCP</h1>
    <h3 class="tagline">First in Mental illness education</h3>
    <p class="description">One of the most prestigous colleges to co-exist in the universe
      <br>Be part of the Anti-silos kilusang paghigugma.
    </p>
  </div>

  <div class="home-container">
    <div class="content-wrapper" id="mainContent">
      <div class="header-container">
        <img src="../Images/iscp-logo.png" alt="ISCP Logo" class="logo">
        <h1>ISCP All-around Portal</h1>
      </div>
      <p class="subheader">Biringan Main Campus</p>

      <!-- Navigation options for login -->
      <button class="home-option" onclick="showLogin('admin')">Log in as Admin</button>
      <button class="home-option" onclick="showLogin('student')">Log in as Student</button>
      <button class="home-option" onclick="showLogin('teacher')">Log in as Teacher</button>
    </div>

    <!-- Admin Login Form -->
    <div class="login-form" id="adminLogin">
      <h2>Administrator Login</h2>
      <form onsubmit="return handleLogin('admin', event)">
        <input type="text" placeholder="Admin Email" required>
        <input type="password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>
      <button class="back-button" onclick="showMain()">← Back to Main Menu</button>
    </div>

    <!-- Student Login Form -->
    <div class="login-form" id="studentLogin">
      <h2>Student Login</h2>
      <form onsubmit="return handleLogin('student', event)">
        <input type="email" name="email" placeholder="Student Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>
      <button class="back-button" onclick="showMain()">← Back to Main Menu</button>
    </div>

    <!-- Teacher Login Form -->
    <div class="login-form" id="teacherLogin">
      <h2>Teacher Login</h2>
      <form id="teacherLoginForm" onsubmit="return handleLogin('teacher', event)">
        <input type="text" placeholder="Teacher Email" required>
        <input type="password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>
      <button class="back-button" onclick="showMain()">← Back to Main Menu</button>
    </div>
    <a href="/CST5-PROJECT/index.php" class="back-to-landing">Back to Landing Page</a>
  </div>

  <div id="customAlert" class="custom-alert">
    <p class="message"></p>
  </div>

  <footer>
    <p>&copy; 2025 ISCP All-around Portal</p>
  </footer>

  <script>
    function showLogin(type) {
      const mainContent = document.getElementById('mainContent');
      const loginForm = document.getElementById(`${type}Login`);
      
      mainContent.classList.add('slide-out');
      
      setTimeout(() => {
        loginForm.style.display = 'flex';
        requestAnimationFrame(() => {
          loginForm.classList.add('active');
        });
      }, 300);
    }

    function showMain() {
      const mainContent = document.getElementById('mainContent');
      const loginForms = document.querySelectorAll('.login-form');
      
      loginForms.forEach(form => {
        form.classList.remove('active');
      });
      
      setTimeout(() => {
        loginForms.forEach(form => {
          form.style.display = 'none';
        });
        mainContent.classList.remove('slide-out');
      }, 300);
    }

    // Replace the existing handleLogin function with this:
function handleLogin(type, event) {
    event.preventDefault();
    console.log('Login attempt started...');
    
    const form = event.target;
    // Fix the email input selection
    const emailInput = form.querySelector('input[name="email"], input[type="text"]');
    const passwordInput = form.querySelector('input[type="password"]');
    
    if (!emailInput || !passwordInput) {
        console.error('Form inputs not found');
        return false;
    }

    const email = emailInput.value;
    const password = passwordInput.value;

    console.log('Email:', email);
    console.log('Type:', type);

    if (!email || !password) {
        showCustomAlert('Please enter both email and password', 'error');
        return false;
    }

    // Set the correct endpoints
    let endpoint = '';
    switch(type) {
        case 'student':
            endpoint = 'student_login.php';
            break;
        case 'admin':
            endpoint = 'admin_login.php';
            break;
        case 'teacher':
            endpoint = 'professor_login.php';
            break;
    }

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        
        if (data.status === 'success') {
            showCustomAlert('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showCustomAlert(data.message || 'Login failed', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showCustomAlert('An error occurred during login', 'error');
    });

    return false;
}
</script>

<script>
function showCustomAlert(message, type) {
    const alertBox = document.getElementById('customAlert');
    const messageElement = alertBox.querySelector('.message');
    
    // Remove any existing classes
    alertBox.classList.remove('error', 'success');
    
    // Add the appropriate class based on type
    if (type) {
        alertBox.classList.add(type);
    }
    
    // Set the message
    messageElement.textContent = message;
    
    // Show the alert
    alertBox.classList.add('show');
    
    // Hide the alert after 3 seconds
    setTimeout(() => {
        alertBox.classList.remove('show');
    }, 3000);
}
</script>
</body>
</html>