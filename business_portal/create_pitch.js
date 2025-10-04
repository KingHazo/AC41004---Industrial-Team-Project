// create_pitch.js
const modal = document.getElementById('ai-modal');
const aiBtn = document.querySelector('.ai-btn');
const closeBtn = document.querySelector('.close-btn');
const submitAnywayBtn = document.getElementById('submit-anyway');
const applyAllBtn = document.getElementById('apply-all');

let latestAnalysis = null;

// Function to run AI analysis
async function runAnalysis(title, elevator, details) {
    const rag = document.getElementById('rag');
    const feedbackList = document.getElementById('ai-feedback');

    // Show loading
    rag.textContent = "Analyzing...";
    rag.className = "";
    feedbackList.innerHTML = "<p>Please wait, running AI analysis...</p>";

    try {
        // Call backend (AI Analysis)
        const res = await fetch("https://air-service-backend-147cb4fc9b81.herokuapp.com/analyze", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ title, elevator, details })
        });

        if (!res.ok) throw new Error("AI service failed");

        // Parse response
        const data = await res.json();
        latestAnalysis = data;

        rag.textContent = data.RAG;
        rag.className = data.RAG.toLowerCase();

        feedbackList.innerHTML = "";

        // Loop through each field
        for (const field in data.analysis) {
            const { original, feedback, suggestion } = data.analysis[field];

            const block = document.createElement("div");
            block.className = "feedback-block";
            block.innerHTML = `
                <h4>${field.toUpperCase()}</h4>
                <p class="feedback-original"><strong>Original:</strong> ${original}</p>
                <p class="feedback-message">⚠️ ${feedback}</p>
                <p class="feedback-suggestion">✅ ${suggestion}</p>
                <div class="feedback-actions">
                  <button class="apply-btn" data-field="${field}">Apply Feedback</button>
                  <button class="apply-rerun-btn" data-field="${field}">Apply & Re-run</button>
                </div>
            `;
            feedbackList.appendChild(block);
        }

        // Adding  event listeners for "Apply" buttons
        feedbackList.querySelectorAll(".apply-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const field = btn.dataset.field;
                if (!latestAnalysis || !latestAnalysis.analysis[field]) return;

                document.getElementById(field).value = latestAnalysis.analysis[field].suggestion;

                Toastify({
                    text: `✅ Applied feedback for ${field.toUpperCase()}`,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#4CAF50"
                }).showToast();
            });
        });

        // Adding  event listeners for "Apply & Re-run" buttons
        feedbackList.querySelectorAll(".apply-rerun-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const field = btn.dataset.field;
                if (!latestAnalysis || !latestAnalysis.analysis[field]) return;

                document.getElementById(field).value = latestAnalysis.analysis[field].suggestion;

                Toastify({
                    text: `🔄 Applied & re-running AI for ${field.toUpperCase()}...`,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#2196F3"
                }).showToast();

                const newTitle = document.getElementById('title').value;
                const newElevator = document.getElementById('elevator').value;
                const newDetails = document.getElementById('details').value;
                runAnalysis(newTitle, newElevator, newDetails);
            });
        });

    } catch (err) {
        console.error("AI error:", err);
        rag.textContent = "Error";
        feedbackList.innerHTML = "<p>AI service not available. Please try again later.</p>";

        Toastify({
            text: "❌ AI service not available. Please try again later.",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "#B00020",
            close: true
        }).showToast();
    }
}

// Open modal and run AI analysis
if (aiBtn) {
    aiBtn.addEventListener('click', () => {
        modal.style.display = "flex";
        const title = document.getElementById('title').value;
        const elevator = document.getElementById('elevator').value;
        const details = document.getElementById('details').value;
        runAnalysis(title, elevator, details);
    });
}

// Close modal
if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        modal.style.display = "none";
    });
}

// ✅ Apply All & Re-run
if (applyAllBtn) {
    applyAllBtn.addEventListener('click', () => {
        if (!latestAnalysis) {
            Toastify({
                text: "⚠️ No AI suggestions available yet.",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#f39c12",
            }).showToast();
            return;
        }

        document.getElementById('title').value = latestAnalysis.analysis.title.suggestion;
        document.getElementById('elevator').value = latestAnalysis.analysis.elevator.suggestion;
        document.getElementById('details').value = latestAnalysis.analysis.details.suggestion;

        Toastify({
            text: "✅ Applied all AI suggestions! Re-running analysis...",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "#4CAF50"
        }).showToast();

        runAnalysis(
            latestAnalysis.analysis.title.suggestion,
            latestAnalysis.analysis.elevator.suggestion,
            latestAnalysis.analysis.details.suggestion
        );
    });
}

// Submit Anyway
if (submitAnywayBtn) {
    submitAnywayBtn.addEventListener('click', async () => {
        modal.style.display = "none";

        const title = document.getElementById('title').value;
        const elevator = document.getElementById('elevator').value;
        const details = document.getElementById('details').value;

        try {
            const res = await fetch("https://air-service-backend-147cb4fc9b81.herokuapp.com/submit-pitch", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ title, elevator, details })
            });

            if (!res.ok) throw new Error("Failed to submit pitch");

            const data = await res.json();

            Toastify({
                text: "✅ " + data.message,
                duration: 5000,
                gravity: "top",
                position: "right",
                backgroundColor: "#4CAF50",
                close: true
            }).showToast();

            document.querySelector('.pitch-form').reset();

        } catch (err) {
            console.error("Submission error:", err);

            Toastify({
                text: "❌ Failed to submit pitch. Please try again.",
                duration: 5000,
                gravity: "top",
                position: "right",
                backgroundColor: "#B00020",
                close: true
            }).showToast();
        }
    });
}
