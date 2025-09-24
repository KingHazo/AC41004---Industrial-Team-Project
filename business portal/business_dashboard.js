const viewButtons = document.querySelectorAll('.view-btn');
const editButtons = document.querySelectorAll('.edit-btn');
const profitButtons = document.querySelectorAll('.profit-btn');

viewButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    window.location.href = 'pitch_details.html';
  });
});

editButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    alert("Edit pitch (to be implemented)");
  });
});

profitButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    window.location.href = 'profit_declare.html';
  });
});