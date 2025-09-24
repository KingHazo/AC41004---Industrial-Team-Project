
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

// Dummy form submission
const form = document.querySelector('.pitch-form');
if (form) {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    alert("Pitch submitted successfully (test mode).");
  });
}