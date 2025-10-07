// Change logo
document.getElementById('change-logo')?.addEventListener('click', () => {
    alert('Logo uploader coming soon');
});

// Save details
document.getElementById('save-details')?.addEventListener('click', () => {
    alert('Business details saved');
});

// add team members
document.getElementById('add-team')?.addEventListener('click', () => {
    const name = document.getElementById('team-name').value.trim();
    const role = document.getElementById('team-role').value.trim();
    if (!name || !role) { alert('Please enter name and role'); return; }
    const li = document.createElement('li');
    li.innerHTML = `<strong>${name}</strong> — ${role}`;
    document.getElementById('team-list').appendChild(li);
    document.getElementById('team-name').value = '';
    document.getElementById('team-role').value = '';
});

// Save banking
document.getElementById('save-banking')?.addEventListener('click', () => {
    const sort = (document.getElementById('sort-code').value || '').replace(/\s/g, '');
    const acct = (document.getElementById('acct-number').value || '').replace(/\s/g, '');
    if (sort && !/^\d{2}-?\d{2}-?\d{2}$/.test(sort)) { alert('Enter a valid sort code (e.g., 12-34-56).'); return; }
    if (acct && !/^\d{6,10}$/.test(acct)) { alert('Enter a valid account number (6–10 digits).'); return; }
    alert('Banking settings saved (mock).');
});

// Upload document
document.getElementById('upload-doc')?.addEventListener('click', () => {
    alert('document upload coming soo');
});

// Open modal on button click
document.getElementById('close-account').addEventListener('click', function() {
    document.getElementById('contactModal').style.display = 'flex';
});

// Close modal on OK button click
document.getElementById('modal-ok').addEventListener('click', function() {
    document.getElementById('contactModal').style.display = 'none';
});

// Optional: Close modal when clicking outside content
window.addEventListener('click', function(e) {
    if (e.target == document.getElementById('contactModal')) {
        document.getElementById('contactModal').style.display = 'none';
    }
});