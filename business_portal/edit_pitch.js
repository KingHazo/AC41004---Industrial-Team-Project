//function to limit the tags
function limitTags(checkbox) {
  const checkboxes = document.querySelectorAll('input[name="tags[]"]');
  const checked = Array.from(checkboxes).filter(cb => cb.checked);

  if (checked.length > 5) {
    checkbox.checked = false; // uncheck the last one
    const msg = document.getElementById('errorMessage');
    msg.style.display = 'block';
    setTimeout(() => {
      msg.style.display = 'none';
    }, 3000);
  }
}

document.querySelectorAll('.dropdown-content').forEach(drop => {
  drop.addEventListener('click', function(event) {
    event.stopPropagation(); // prevents the click from bubbling up
  });
});
