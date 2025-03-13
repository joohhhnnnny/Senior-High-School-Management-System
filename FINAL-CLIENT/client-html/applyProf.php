<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISCP Teachers Application Form</title>
  <link rel="stylesheet" href="../client-css/applyProf.css"> <!-- Ensure the correct path to your CSS file -->
</head>
<body>
  <div class="container">
    <h2>ISCP Teachers Application Form</h2>
    <form method="POST" enctype="multipart/form-data" autocomplete="off" spellcheck="false"> <!-- Ensure the correct action path -->
      <div class="form-group">
        <label for="fullName">Full Name</label>
        <input type="text" id="fullName" name="fullName" required autocomplete="off" readonly 
               onfocus="this.removeAttribute('readonly');" aria-labelledby="fullName">
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required autocomplete="off" readonly 
               onfocus="this.removeAttribute('readonly');" aria-labelledby="phone">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autocomplete="off" readonly 
               onfocus="this.removeAttribute('readonly');" aria-labelledby="email">
      </div>
      <div class="form-group">
        <label for="resume">Upload Resume</label>
        <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx"  autocomplete="off" required aria-labelledby="resume">
      </div>
      <div class="form-group button-group">
        <input class="apply" type="submit" value="Apply" id="apply">
      </div>
    </form>
  </div>

  <script src="../client-js/applyProf.js"></script> <!-- Ensure the correct path to your JS file -->
</body>
</html>