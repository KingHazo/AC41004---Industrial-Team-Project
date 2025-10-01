document.getElementById('change-photo')?.addEventListener('click', () => {
    alert('Photo upload coming soon');
});

document.getElementById('upload-docs')?.addEventListener('click', () => {
    alert('Document uploader coming soon');
});

document.getElementById('edit-profile')?.addEventListener('click', () => {
    alert('Redirecting to profile editor');
    // window.location.href = 'investor-settings.html';
});

document.getElementById('deactivate-account')?.addEventListener('click', () => {
    if (confirm('Are you sure you want to deactivate your account? This cannot be undone.')) {
        alert('Account deactivated');
        window.location.href = '../login.html';
    }
});