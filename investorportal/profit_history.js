// test data
const payouts = [
    {
        date: '2025-09-01',
        pitch: 'EcoBottle – Smart Reusable Bottle',
        period: 'Q2 2025',
        amount: 180.00,
        note: 'Quarterly distribution',
        status: 'paid'
    },

    {
        date: '2025-08-15',
        pitch: 'Startup B',
        period: 'Q2 2025',
        amount: 120.00,
        note: 'Late adjustment',
        status: 'paid'
    },

    {
        date: '2025-07-02',
        pitch: 'Startup C',
        period: 'Q2 2025',
        amount: 0.00,
        note: 'No distribution',
        status: 'pending'
    },

    {
        date: '2025-05-12',
        pitch: 'EcoBottle – Smart Reusable Bottle',
        period: 'Q1 2025',
        amount: 95.50,
        note: 'First payout',
        status: 'paid'
    },

    {
        date: '2024-12-20',
        pitch: 'Startup B',
        period: 'FY 2024',
        amount: 244.50,
        note: 'Year-end distribution',
        status: 'paid'
    }
];

// elements
const tbody = document.getElementById('history-body');
const noResults = document.getElementById('no-results');
const kpiTotal = document.getElementById('kpi-total');
const kpiCount = document.getElementById('kpi-count');
const kpiLast = document.getElementById('kpi-last');
const chips = document.querySelectorAll('.chip');
const searchEl = document.getElementById('search');
// const exportBtn = document.getElementById('export');

// helpers 
function withinRange(dateStr, days) {
    if (days === 'all') return true;
    const target = new Date(dateStr);
    const now = new Date();
    const diff = (now - target) / (1000 * 60 * 60 * 24);
    return diff <= Number(days);
}

function formatGBP(n) {
    return `£${Number(n).toFixed(2)}`;
}

function renderRows(rows) {
    tbody.innerHTML = '';
    rows.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td>${r.date}</td>
      <td>${r.pitch}</td>
      <td>${r.period}</td>
      <td class="right">${formatGBP(r.amount)}</td>
      <td>${r.note || ''}</td>
      <td><span class="badge ${r.status}">${r.status.charAt(0).toUpperCase() + r.status.slice(1)}</span></td>
    `;
        tbody.appendChild(tr);
    });
    noResults.hidden = rows.length > 0;
}

function updateKPIs(rows) {
    const total = rows.reduce((sum, r) => sum + (r.status === 'paid' ? r.amount : 0), 0);
    kpiTotal.textContent = formatGBP(total);
    kpiCount.textContent = rows.length.toString();
    const latest = rows
        .filter(r => r.status === 'paid')
        .map(r => r.date)
        .sort((a, b) => new Date(b) - new Date(a))[0];
    kpiLast.textContent = latest || '—';
}

function applyFilters() {
    const activeChip = document.querySelector('.chip.active');
    const range = activeChip?.dataset.range || 'all';
    const q = (searchEl.value || '').toLowerCase();

    const filtered = payouts.filter(p =>
        withinRange(p.date, range) &&
        (p.pitch.toLowerCase().includes(q) || (p.note || '').toLowerCase().includes(q))
    );

    renderRows(filtered);
    updateKPIs(filtered);
}

// events
chips.forEach(ch => {
    ch.addEventListener('click', () => {
        chips.forEach(x => x.classList.remove('active'));
        ch.classList.add('active');
        applyFilters();
    });
});

searchEl.addEventListener('input', () => {
    applyFilters();
});

// exportBtn.addEventListener('click', () => {
//     const activeRows = Array.from(tbody.querySelectorAll('tr')).map(tr =>
//         Array.from(tr.children).map(td => `"${td.textContent.replace(/"/g, '""')}"`).join(',')
//     );
//     const header = ['Date', 'Pitch', 'Period', 'Amount', 'Note', 'Status'].join(',');
//     const csv = [header, ...activeRows].join('\n');
//     const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
//     const url = URL.createObjectURL(blob);
//     const a = document.createElement('a');
//     a.href = url; a.download = 'profit_history.csv';
//     document.body.appendChild(a); a.click(); a.remove();
//     URL.revokeObjectURL(url);
// });

applyFilters();