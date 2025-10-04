
const modal = document.getElementById('ai-modal');
const aiBtn = document.querySelector('.ai-btn');
const closeBtn = document.querySelector('.close-btn');
const reanalyseBtn = document.getElementById('reanalyse');
const submitAnywayBtn = document.getElementById('submit-anyway');


if (aiBtn) {
  aiBtn.addEventListener('click', () => {
    modal.style.display = "flex";

    // Example dynamic RAG
    const rag = document.getElementById('rag');
    const scores = ["green", "amber", "red"];
    const random = scores[Math.floor(Math.random() * scores.length)];
    rag.className = random;
    rag.textContent = random.charAt(0).toUpperCase() + random.slice(1);
  });
}

// Close modal
if (closeBtn) {
  closeBtn.addEventListener('click', () => {
    modal.style.display = "none";
  });
}

// re run analysis
if (reanalyseBtn) {
  reanalyseBtn.addEventListener('click', () => {
    alert("Re-running AI analysis (placeholder).");
  });
}

// Submit anyway
if (submitAnywayBtn) {
  submitAnywayBtn.addEventListener('click', () => {
    modal.style.display = "none";
    alert("Pitch submitted despite AI warnings (test mode).");
  });
}


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
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // remove active class from all
        document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
        // add active to clicked
        btn.classList.add('active');
        // set hidden input value
        document.getElementById('payout_frequency').value = btn.dataset.value;
    });
});

//to change the status from draft to status
function submitPitch(status) {
    document.getElementById('status').value = status;
    document.querySelector('.pitch-form').submit();
}