// tier detection + shares calc
const amountInput = document.getElementById('invest-amount');
const detectedTierEl = document.getElementById('detected-tier');
const detectedMultEl = document.getElementById('detected-mult');
const calcSharesEl = document.getElementById('calc-shares');

function detectTier(amount) {
    const rows = Array.from(document.querySelectorAll('#tiers-table tbody tr'));
    for (const r of rows) {
        const min = parseFloat(r.dataset.min);
        const max = parseFloat(r.dataset.max);
        const mult = parseFloat(r.dataset.mult);
        if (amount >= min && amount <= max) {
            return { tier: r.dataset.tier, mult };
        }
    }
    return null;
}

function updatePreview() {
    const amt = parseFloat(amountInput.value);
    if (isNaN(amt) || amt <= 0) {
        detectedTierEl.textContent = '—';
        detectedMultEl.textContent = '—';
        calcSharesEl.textContent = '—';
        return;
    }
    const match = detectTier(amt);
    if (!match) {
        detectedTierEl.textContent = 'Out of range';
        detectedMultEl.textContent = '—';
        calcSharesEl.textContent = '—';
        return;
    }
    detectedTierEl.textContent = match.tier;
    detectedMultEl.textContent = match.mult.toFixed(1);
    const shares = Math.round(amt * match.mult);
    calcSharesEl.textContent = shares.toString();
}

if (amountInput) {
    amountInput.addEventListener('input', updatePreview);
    updatePreview();
}

// handle invest submit
const form = document.getElementById('invest-form');
form?.addEventListener('submit', (e) => {
    e.preventDefault();
    const amt = parseFloat(amountInput.value);
    const match = detectTier(amt || 0);
    if (!amt || !match) {
        alert('Please enter a valid amount within a tier range.');
        return;
    }
    alert(`Investment confirmed: £${amt.toFixed(2)} (${match.tier}, x${match.mult}).`);
});

// Allow cancel (before close)
document.getElementById('cancel-investment')?.addEventListener('click', () => {
    alert('Investment cancelled (test mode).');
});

