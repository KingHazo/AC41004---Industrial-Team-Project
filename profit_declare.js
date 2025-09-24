// Auto-update footer year
let year = document.querySelector('.day');
if (year) {
  year.textContent = new Date().getFullYear();
}

// Calculate distributable profit
const profitInput = document.getElementById('profit');
const distributableField = document.getElementById('distributable');
const profitForm = document.querySelector('.profit-form');

// Example profit share (in real app this should come from DB)
const investorSharePercent = 20;

if (profitInput && distributableField) {
  profitInput.addEventListener('input', () => {
    const profit = parseFloat(profitInput.value) || 0;
    const distributable = (profit * investorSharePercent) / 100;
    distributableField.value = `£${distributable.toFixed(2)}`;
  });
}

// Submit handler
if (profitForm) {
  profitForm.addEventListener('submit', (e) => {
    e.preventDefault();
    alert("Profits distributed successfully! (test mode)");
  });
}