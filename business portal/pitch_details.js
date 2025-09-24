let year = document.querySelector('.day');
if (year) {
  year.textContent = new Date().getFullYear();
}

// example button actions
const editBtn = document.querySelector('.edit-btn');
const profitBtn = document.querySelector('.profit-btn');

if (editBtn) {
  editBtn.addEventListener('click', () => {
    alert("Redirecting to edit pitch (placeholder).");
    //window.location.href = "edit-pitch.html";
  });
}

if (profitBtn) {
  profitBtn.addEventListener('click', () => {
    window.location.href = "profit_declare.html";
  });
}