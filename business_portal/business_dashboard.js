const viewButtons = document.querySelectorAll('.view-btn');
const profitButtons = document.querySelectorAll('.profit-btn');

viewButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    window.location.href = 'pitch_details.php';
  });
});

profitButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    window.location.href = 'profit_declare.php';
  });
});