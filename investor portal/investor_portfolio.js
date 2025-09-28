// ----- Config -----
const INVESTOR_ID = 'demo-investor-001';

// when the really API is ready  we  change this base:
const API_BASE = 'http://localhost:4000/api'; // placeholder
const PORTFOLIO_URL = `${API_BASE}/portfolio/${INVESTOR_ID}`;
const WITHDRAW_URL = `${API_BASE}/withdraw`;

// ----- Elements -----
const tbody = document.getElementById('portfolio-body');
const balanceEl = document.getElementById('acct-balance');
const withdrawBtn = document.getElementById('withdraw-btn');

// ----- Mock data (used if backend not ready) -----
const mockPortfolio = {
    balance: 240,
    investments: [
        { pitchTitle: "EcoCup Cafes", amount: 150, tier: "Silver", date: "2025-09-20", roi: 18 },
        { pitchTitle: "SmartFarm IoT", amount: 250, tier: "Gold", date: "2025-09-21", roi: 42 },
        { pitchTitle: "GreenDelivery", amount: 100, tier: "Bronze", date: "2025-09-24", roi: 7 }
    ]
};

// ----- Helpers -----
const money = (n) => `£${(Number(n) || 0).toFixed(2)}`;

function renderPortfolio(data) {
    // Balance
    balanceEl.textContent = money(data.balance);

    // Table rows
    tbody.innerHTML = '';
    let totalInvested = 0;
    let totalROI = 0;

    data.investments.forEach(inv => {
        totalInvested += inv.amount;
        totalROI += inv.roi;

        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td>${inv.pitchTitle}</td>
        <td>${money(inv.amount)}</td>
        <td>${inv.tier}</td>
        <td>${inv.date}</td>
        <td>${inv.roi.toFixed(2)}%</td>
      `;
        tbody.appendChild(tr);
    });

    // Update totals
    document.getElementById('total-invested').textContent = money(totalInvested);
    document.getElementById('avg-roi').textContent = `${(totalROI / data.investments.length).toFixed(2)}%`;

    // Enable/disable withdraw
    withdrawBtn.disabled = (data.balance <= 0);
}


async function fetchPortfolio() {
    try {
        const res = await fetch(PORTFOLIO_URL);
        if (!res.ok) throw new Error('API not ready');
        return await res.json();
    } catch {
        // fallback to mock for demo
        return mockPortfolio;
    }
}

async function loadPortfolio() {
    const data = await fetchPortfolio();
    renderPortfolio(data);
}

// Withdraw (mock first, real when API exists)
withdrawBtn.addEventListener('click', async () => {
    const amountStr = prompt("Enter amount to withdraw (GBP):", "50");
    if (!amountStr) return;

    const amount = Number(amountStr);
    const currentBal = Number(balanceEl.textContent.replace(/[£,]/g, ''));

    if (isNaN(amount) || amount <= 0) return alert("Please enter a valid amount.");
    if (amount > currentBal) return alert("Amount exceeds available balance.");

    // Try real API
    try {
        const res = await fetch(WITHDRAW_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ investorId: INVESTOR_ID, amount })
        });
        if (!res.ok) throw new Error('Withdraw API failed');
        await loadPortfolio();
        alert("Withdrawal successful (backend).");
    } catch {
        // Local simulation if backend not ready
        balanceEl.textContent = money(currentBal - amount);
        withdrawBtn.disabled = (currentBal - amount) <= 0;
        alert("Withdrawal simulated locally (no backend yet).");
    }
});


loadPortfolio();
