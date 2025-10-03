// Auto-update footer year
let year = document.querySelector('.day');
if (year) {
  year.textContent = new Date().getFullYear();
}
