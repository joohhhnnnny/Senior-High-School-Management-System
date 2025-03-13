document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(form);
            console.log('Form data prepared:', Object.fromEntries(formData));
            
            const response = await fetch('../client-php/applyProf.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const result = await response.json();
                if (result.success) {
                    alert('Application submitted successfully!');
                    form.reset();
                } else {
                    alert(result.message || 'Submission failed. Please try again.');
                }
            } else {
                // Handle non-JSON response
                const text = await response.text();
                console.error('Server returned non-JSON response:', text);
                alert('Server error. Please try again later.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again later.');
        }
    });
});