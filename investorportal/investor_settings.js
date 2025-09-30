// --- Profile ---
document.getElementById('profile-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Profile saved (test mode).');
});

// --- password change ---
document.getElementById('password-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const current = document.getElementById('current-password').value.trim();
    const next = document.getElementById('new-password').value.trim();
    const confirm = document.getElementById('confirm-password').value.trim();

    if (next.length < 8) {
        alert('New password should be at least 8 characters.');
        return;
    }
    if (next !== confirm) {
        alert('New password and confirmation do not match.');
        return;
    }
    if (current === next) {
        alert('New password must differ from current password.');
        return;
    }
    alert('Password changed (test mode).');
    e.target.reset();
});


document.getElementById('bank-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const sort = document.getElementById('sort-code').value.replace(/\s/g, '');
    const acc = document.getElementById('account-number').value.replace(/\s/g, '');

    if (sort && !/^\d{2}-?\d{2}-?\d{2}$/.test(sort)) {
        alert('Please enter a valid UK sort code (e.g., 12-34-56).');
        return;
    }
    if (acc && !/^\d{6,10}$/.test(acc)) {
        alert('Please enter a valid account number (6–10 digits).');
        return;
    }
    alert('Payment settings saved (mock).');
});


document.getElementById('notify-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const prefs = {
        investments: document.getElementById('notify-investments').checked,
        payouts: document.getElementById('notify-payouts').checked,
        news: document.getElementById('notify-news').checked,
    };
    console.log('Saved notification prefs:', prefs);
    alert('Notification settings saved.');
});


document.getElementById('close-account')?.addEventListener('click', () => {
    if (confirm('Are you sure you want to close your account? This cannot be undone.')) {
        alert('Account closed (test mode).');
        window.location.href = '../login.php';
    }
});