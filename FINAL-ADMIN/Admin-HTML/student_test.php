<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Edit Test</title>
</head>
<body>
    <div id="editModal" class="student-modal">
        <div class="modal-wrapper">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Student Test</h2>
                </div>
                <div class="modal-body">
                    <form id="editStudentForm" novalidate>
                        <input type="hidden" id="editId" value="27">  <!-- This is john's ID from your data -->
                        <div class="form-group">
                            <label for="editFullname">Full Name</label>
                            <input type="text" id="editFullname" value="john">
                        </div>
                        <div class="form-group">
                            <label for="editEmail">Email</label>
                            <input type="email" id="editEmail" value="johnbongcacjohn@gmail.com">
                        </div>
                        <div class="form-group">
                            <label for="editAddress">Address</label>
                            <input type="text" id="editAddress" value="biringan po">
                        </div>
                        <div class="form-group">
                            <label for="editPhone">Phone</label>
                            <input type="tel" id="editPhone" value="09207056696">
                        </div>
                        <div class="modal-buttons">
                            <button type="button" id="saveChangesBtn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Clear the console on page load
        console.clear();
        console.log('Test page loaded');

        // Direct event handler setup
        document.getElementById('saveChangesBtn').onclick = async function(e) {
            console.group('Save Button Click Handler');
            console.log('1. Save button clicked');
            
            try {
                // Disable button
                this.disabled = true;
                this.textContent = 'Saving...';
                
                // Get form data
                const formData = {
                    id: document.getElementById('editId').value,
                    fullname: document.getElementById('editFullname').value,
                    email: document.getElementById('editEmail').value,
                    address: document.getElementById('editAddress').value,
                    phoneNumber: document.getElementById('editPhone').value
                };
                
                console.log('2. Form data:', formData);
                
                // Send request
                console.log('3. Sending request...');
                const response = await fetch('../Admin-PHP/student_management_edit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                console.log('4. Response status:', response.status);
                const text = await response.text();
                console.log('5. Raw response:', text);
                
                const result = JSON.parse(text);
                console.log('6. Parsed response:', result);
                
                if (!result.success) {
                    throw new Error(result.message || 'Save failed');
                }
                
                console.log('7. Save successful');
                alert('Student updated successfully');
                
            } catch (error) {
                console.error('❌ Error:', error);
                alert('Error: ' + error.message);
            } finally {
                this.disabled = false;
                this.textContent = 'Save Changes';
                console.groupEnd();
            }
        };
    </script>
</body>
</html>
