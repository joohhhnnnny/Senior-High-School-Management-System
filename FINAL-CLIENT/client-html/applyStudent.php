<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISCP Student Enrollment Form</title>
  <link rel="stylesheet" href="../client-css/applyStudent.css">
</head>
<body>
  <div class="container">
    <h2>ISCP Student Enrollment Form</h2>
    <form id="studentForm" action="../client-php/applyStudent.php" method="POST" enctype="multipart/form-data" autocomplete="off" spellcheck="false">
      <!-- Page 1 -->
      <div class="page" id="page1">
        <div class="form-group">
          <label for="fullName">Full Name</label>
          <input type="text" id="fullName" name="fullName" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
        </div>
        <div class="form-group">
          <label for="birthdate">Date of Birth</label>
          <input type="date" id="birthdate" name="birthdate" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
        </div>
        <div class="form-group">
          <label for="gender">Gender</label>
          <select id="gender" name="gender" required autocomplete="new-gender">
            <option value="" disabled selected hidden>--Select Gender--</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
        </div>
        <div class="form-group button-group">
          <input class="toPage2" type="button" value="Next Page" id="toPage2">
        </div>
      </div>

      <!-- Page 2 -->
      <div class="page" id="page2">
        <div class="form-group">
          <label for="yearLevel">Year Level</label>
          <select id="yearLevel" name="yearLevel" required>
            <option value="" disabled selected hidden>--Select Year Level--</option>
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
          </select>
        </div>
        <div class="form-group">
          <label for="strand">Strand</label>
          <select id="strand" name="strand" required>
            <option value="" disabled selected hidden>--Select Strand--</option>
            <option value="aad">Arts and Design (AAD)</option>
            <option value="humss">Humanities and Social Sciences (HUMSS)</option>
            <option value="ict">Information and Communication Technology (ICT)</option>
          </select>
        </div>
        <div class="form-group">
          <label for="birthCertificate">Upload Birth Certificate</label>
          <input type="file" id="birthCertificate" name="birthCertificate" accept="image/*" required>
        </div>
        <div class="form-group">
          <label for="form138">Upload Form 138</label>
          <input type="file" id="form138" name="form138" accept="image/*" required>
        </div>
        <div class="form-group button-group">
          <input class="toPage1" type="button" value="Previous Page" id="toPage1">
          <input class="toPage3" type="button" value="Next Page" id="toPage3">
        </div>
      </div>

      <!-- Page 3 -->
      <div class="page" id="page3">
        <h4>--Contact In-case of Emergency--</h4>
        <div class="form-group">
          <label for="parentfullName">Parent's Full Name</label>
          <input type="text" id="parentfullName" name="parentfullName" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
        </div>
        <div class="form-group">
          <label for="parentContact">Parent's Contact</label>
          <input type="tel" id="parentContact" name="parentContact" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
        </div>
        <div class="form-group button-group">
          <input class="backToPage2" type="button" value="Previous Page" id="backToPage2">
          <input class="submit" type="submit" value="Submit" id="submit">
        </div>
      </div>

      <!-- Pagination Indicator -->
      <div class="pagination-indicator">
        <span class="page-indicator" id="indicator1"></span>
        <span class="page-indicator" id="indicator2"></span>
        <span class="page-indicator" id="indicator3"></span>
      </div>
    </form>
  </div>

  <!-- Load JavaScript at the end of the body -->
  <script src="../client-js/applyStudent.js?v=123"></script>

</body>
</html>