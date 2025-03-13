<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Sign Up</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- FontAwesome for icons -->
    <style>
        body {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
  background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
    url('school login.jpg') no-repeat center center fixed;
  background-size: cover;
}

.loginContainer {
  position: relative;
  width: 400px;
  height: 450px;
  overflow: hidden; /* Hide the transitioning content */
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 10px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
}

.loginBox,
.signUpBox {
  position: absolute;
  top: 0;
  width: 100%;
  height: 100%;
  transition: transform 0.5s ease-in-out; /* Smooth transition */
}

.loginBox {
  transform: translateX(0); /* Default position */
}

.signUpBox {
  transform: translateX(100%); /* Positioned off-screen */
}

.loginContainer.active .loginBox {
  transform: translateX(-100%); /* Move login box out of view */
}

.loginContainer.active .signUpBox {
  transform: translateX(0); /* Bring signup box into view */
}

h1 {
  margin-top: 50px;
  margin-bottom: 50px;
  font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande',
   'Lucida Sans Unicode', 'Geneva', Verdana, sans-serif;
  font-size: 44px;
  text-align: center;
  color: white;
}

.input-group {
  margin: 15px 0;
  text-align: center;
  position: relative;
  margin-top: 20px;
  margin-left: 10px;
  margin-right: 10px;
}

#password {
  width: 80%;
  padding-right: 20px; /* Make space for the icon */
}

#togglePassword {
  position: absolute;
  right: 10px; /* Position the icon on the right side */
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  z-index: 1; /* Ensure the icon is above the input */
}

.input-group input {
  width: 80%;
  padding: 10px;
  border: none;
  border-radius: 5px;
  outline: none;
}

.loginBtn, .signUpBtn {
  transition: all 0.3s ease; /* Smooth transition for hover effect */
}

.loginBtn:hover, .signUpBtn:hover {
  background-color: #2a4868; /* Change background color on hover */
  color: #fff; /* Change text color on hover */
  transform: scale(1.05); /* Slightly enlarge the button */
}

.options {
  color: #fff;
  position: absolute;
  bottom: 20px;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.signUpLink, .loginLink {
  text-decoration: none;
  color: #007bff; /* Customize the color */
}

.signUpLink:hover, .loginLink:hover {
  text-decoration: underline; /* Optional: underline effect on hover */
}

button {
  display: block;
  margin: 20px auto;
  padding: 10px 50px;
  border: none;
  border-radius: 5px;
  background: linear-gradient(to right, #1b5a92, #836efc);
  color: white;
  cursor: pointer;
  font-size: 16px;
}
    </style>
</head>
<body>

    <div class="loginContainer" id="loginContainer">
        <!-- Login Box -->
        <div class="loginBox">
            <h1>Login</h1>
            <form>
                <div class="input-group">
                    <input type="text" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" id="password" placeholder="Password" required>
                    <i class="fas fa-eye" id="togglePassword"></i> <!-- Eye icon for password toggle -->
                </div>
                <button type="submit" class="loginBtn" onclick="window.location.href='sampleone.html'">Login</button>
            </form>
            <div class="options">
                <p>Don't have an account? <a href="#" class="signUpLink" id="signUpLink">Sign Up</a></p>
            </div>
        </div>

        <!-- Sign Up Box -->
        <div class="signUpBox">
            <h1>Sign Up</h1>
            <form>
                <div class="input-group">
                    <input type="text" placeholder="Full Name" required>
                </div>
                <div class="input-group">
                    <input type="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="password" placeholder="Create Password" required>
                </div>
                <button type="submit" class="signUpBtn">Sign Up</button>
            </form>
            <div class="options">
                <p>Already have an account? <a href="#" class="loginLink" id="loginLink">Login</a></p>
            </div>
        </div>
    </div>

    <script>
        // Toggle between Login and Sign-Up
        const loginContainer = document.getElementById("loginContainer");
        const signUpLink = document.getElementById("signUpLink");
        const loginLink = document.getElementById("loginLink");

        signUpLink.addEventListener("click", (e) => {
            e.preventDefault();
            loginContainer.classList.add("active");
        });

        loginLink.addEventListener("click", (e) => {
            e.preventDefault();
            loginContainer.classList.remove("active");
        });

        // Toggle Password Visibility
        const passwordField = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", () => {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                togglePassword.classList.remove("fa-eye");
                togglePassword.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                togglePassword.classList.remove("fa-eye-slash");
                togglePassword.classList.add("fa-eye");
            }
        });
    </script>

</body>
</html>
