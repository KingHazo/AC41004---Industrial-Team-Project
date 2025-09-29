// View pitch (investor view)
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        window.location.href = 'investor_pitch_details.html'; // to be created next
    });
});

// Cancel investment (this will be allowed before a pitch closes)
document.querySelectorAll('.cancel-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        alert('Investment cancelled (test mode).');
    });
});