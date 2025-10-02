// Function for displaying a simple alert (replace with your proper UI element if available)
const alertMessage = (message, type) => {
    // console.log(`[${type.toUpperCase()}] Profile Action: ${message}`);

    const alertBox = document.createElement('div');
    alertBox.textContent = message;
    alertBox.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 10px 20px; 
        background-color: ${type === 'success' ? '#4CAF50' : '#f44336'}; 
        color: white; z-index: 10000; border-radius: 5px; opacity: 1; transition: opacity 0.5s;
    `;
    document.body.appendChild(alertBox);
    setTimeout(() => {
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
    }, 3000);
};


document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('edit-profile-modal');
    // *** CHANGED: Target the existing 'Edit Profile' button
    const editProfileButton = document.getElementById('edit-profile'); 
    
    // *** REMOVED: No longer need document.getElementById('edit-details-btn')
    
    const closeButtons = editModal.querySelectorAll('.close-btn');
    const editForm = document.getElementById('edit-profile-form');

    // === PLACEHOLDER/OTHER ACTIONS ===
    
    document.getElementById('change-photo')?.addEventListener('click', () => {
        alert('Photo upload coming soon');
    });
    
    document.getElementById('upload-docs')?.addEventListener('click', () => {
        alert('Document uploader coming soon');
    });
    
    document.getElementById('deactivate-account')?.addEventListener('click', () => {
        if (confirm('Are you sure you want to deactivate your account? This cannot be undone.')) {
            alert('Account deactivated');
            window.location.href = '../login.html';
        }
    });

    document.getElementById('add-funds-btn')?.addEventListener('click', () => {
        alert('Add Funds functionality is coming soon.');
    });

    // === MODAL OPEN/CLOSE HANDLERS ===
    
    // *** CHANGED: Open the modal when 'Edit Profile' is clicked
    editProfileButton?.addEventListener('click', () => {
        editModal.style.display = 'block';
    });

    // Close the modal
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            editModal.style.display = 'none';
        });
    });

    // Close if user clicks outside of it
    window.addEventListener('click', (event) => {
        if (event.target === editModal) {
            editModal.style.display = 'none';
        }
    });

    // === PROFILE UPDATE FORM SUBMISSION (AJAX) ===
    editForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const saveButton = editForm.querySelector('button[type="submit"]');
        const originalText = saveButton.textContent;
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        // get form data
        const formData = new URLSearchParams(new FormData(editForm));

        try {
            const response = await fetch('update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alertMessage(result.message, 'success');
                editModal.style.display = 'none';
                
                // Reload the page to display the new data
                setTimeout(() => window.location.reload(), 500);

            } else {
                alertMessage(result.message, 'error');
            }

        } catch (error) {
            console.error('Profile update failed:', error);
            alertMessage('An unexpected error occurred. Please try again.', 'error');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = originalText;
        }
    });
});