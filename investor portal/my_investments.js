// filter chips
const chips = document.querySelectorAll('.chip');
const list = document.getElementById('investments');

function applyFilter(status) {
    Array.from(list.children).forEach(card => {
        const s = card.getAttribute('data-status'); // active | funded | closed
        const show = (status === 'all') ? true : (s === status);
        card.style.display = show ? '' : 'none';
    });
}

chips.forEach(c => {
    c.addEventListener('click', () => {
        chips.forEach(x => x.classList.remove('active'));
        c.classList.add('active');
        applyFilter(c.dataset.filter);
    });
});

applyFilter('all');

list.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const id = btn.dataset.id;
    const action = btn.dataset.action;

    if (action === 'view') {
        window.location.href = 'investor-pitch-details.html';
    }

    if (action === 'cancel') {
        const card = btn.closest('.inv-card');
        const status = card?.getAttribute('data-status');
        if (status !== 'active') {
            alert('You can only cancel while the pitch is still active.');
            return;
        }
        if (confirm('Cancel this investment? You can only cancel before the funding window closes.')) {
            alert('Investment cancelled (test mode).');
        }
    }
});