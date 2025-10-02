console.log("pitch_details.js loaded");

let year = document.querySelector('.day');
if (year) {
  year.textContent = new Date().getFullYear();
}



if (profitBtn) {
  profitBtn.addEventListener('click', () => {
    window.location.href = "profit_declare.php";
  });
}