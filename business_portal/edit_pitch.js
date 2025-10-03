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



//selects payout frequency
document.addEventListener('DOMContentLoaded', () => {
    const payoutButtons = document.querySelectorAll('.toggle-btn');
    const hiddenInput = document.getElementById('payout_frequency');

    // set hidden input to active when the page loads
    const activeBtn = document.querySelector('.toggle-btn.active');
    if (activeBtn) hiddenInput.value = activeBtn.dataset.value;

    // only add click listeners if the buttons are not disabled
    payoutButtons.forEach(btn => {
        if (!btn.disabled) {
            btn.addEventListener('click', () => {
                payoutButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                hiddenInput.value = btn.dataset.value;
            });
        }
    });
});

