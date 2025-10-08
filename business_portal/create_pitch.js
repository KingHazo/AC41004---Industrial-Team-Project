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

            // Determine if suggestion is different from original
            const hasFeedback = feedback && feedback.trim().length > 0;
            const isSuggestionDifferent = suggestion && suggestion.trim() !== original.trim();

            const showButtons = hasFeedback && isSuggestionDifferent;

            block.innerHTML = `
                <h4>${field.toUpperCase()}</h4>
                <p class="feedback-original"><strong>Original:</strong> ${original}</p>
                ${showButtons
                    ? `
                    <p class="feedback-message">⚠️ ${feedback}</p>
                    <p class="feedback-suggestion">✅ ${suggestion}</p>
                    <div class="feedback-actions">
                        <button class="apply-btn" data-field="${field}">Apply Feedback</button>
                        <button class="apply-rerun-btn" data-field="${field}">Apply & Re-run</button>
                    </div>
                    `
                    : `<p class="feedback-message text-success">✅ No changes suggested.</p>`
                }
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
    drop.addEventListener('click', function (event) {
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


// to handle errors
document.querySelector(".pitch-form").addEventListener("submit", function (e) {
    let errorMsg = "";

    const title = document.getElementById("title").value.trim();
    const elevator = document.getElementById("elevator").value.trim();
    const details = document.getElementById("details").value.trim();
    const target = parseFloat(document.getElementById("target").value);
    const endDate = document.getElementById("end-date").value;
    const profitShare = parseFloat(document.getElementById("profit-share").value);

    // Limits
    const maxTitle = 50, maxElevator = 250, maxDetails = 1000;
    const minTarget = 1000, maxTarget = 100000000;
    const minProfitShare = 1, maxProfitShare = 100;
    const maxTierName = 30, minTierAmount = 1, maxTierAmount = 100000000;
    const minMultiplier = 0.1, maxMultiplier = 10;

    // Date validation
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const minDate = new Date(today); minDate.setDate(minDate.getDate() + 1);
    const selectedDate = new Date(endDate);

    if (!title) errorMsg = "Please enter a title for your pitch.";
    else if (title.length > maxTitle) errorMsg = `Title cannot exceed ${maxTitle} characters.`;
    else if (!elevator) errorMsg = "Elevator pitch cannot be empty.";
    else if (elevator.length > maxElevator) errorMsg = `Elevator pitch cannot exceed ${maxElevator} characters.`;
    else if (!details) errorMsg = "Detailed pitch cannot be empty.";
    else if (details.length > maxDetails) errorMsg = `Detailed pitch cannot exceed ${maxDetails} characters.`;
    else if (isNaN(target) || target < minTarget || target > maxTarget) errorMsg = `Target Investment must be between £${minTarget.toLocaleString()} and £${maxTarget.toLocaleString()}.`;
    else if (!endDate) errorMsg = "Please select an investment window end date.";
    else if (selectedDate < minDate) errorMsg = "Investment window end date must be at least one day from today.";
    else if (isNaN(profitShare) || profitShare < minProfitShare || profitShare > maxProfitShare) errorMsg = `Investor Profit Share must be between ${minProfitShare}% and ${maxProfitShare}%.`;

    // Tier validation
    const tierRows = document.querySelectorAll(".tier-row");
    let tiers = [];
    tierRows.forEach((row, index) => {
        const name = row.querySelector('input[name="tier_name[]"]').value.trim();
        const min = parseFloat(row.querySelector('input[name="tier_min[]"]').value);
        const max = parseFloat(row.querySelector('input[name="tier_max[]"]').value);
        const multiplier = parseFloat(row.querySelector('input[name="tier_multiplier[]"]').value);

        if (name || !isNaN(min) || !isNaN(max) || !isNaN(multiplier)) {
            tiers.push({ name, min, max, multiplier, index: index + 1 });
        }
    });

    if (tiers.length === 0) errorMsg = "Please provide at least one investment tier.";
    else {
        for (const t of tiers) {
            if (!t.name || isNaN(t.min) || isNaN(t.max) || isNaN(t.multiplier)) { errorMsg = `Tier ${t.index} is incomplete.`; break; }
            else if (t.name.length > maxTierName) { errorMsg = `Tier ${t.index} name cannot exceed ${maxTierName} characters.`; break; }
            else if (t.min < minTierAmount || t.min > maxTierAmount) { errorMsg = `Tier ${t.index} minimum must be between £${minTierAmount} and £${maxTierAmount}.`; break; }
            else if (t.max < minTierAmount || t.max > maxTierAmount) { errorMsg = `Tier ${t.index} maximum must be between £${minTierAmount} and £${maxTierAmount}.`; break; }
            else if (t.min > t.max) { errorMsg = `Tier ${t.index} minimum cannot exceed maximum.`; break; }
            else if (t.multiplier < minMultiplier || t.multiplier > maxMultiplier) { errorMsg = `Tier ${t.index} multiplier must be between ${minMultiplier} and ${maxMultiplier}.`; break; }
        }

        // Check for overlapping
        if (!errorMsg) {
            tiers.sort((a, b) => a.min - b.min);
            for (let i = 1; i < tiers.length; i++) {
                if (tiers[i].min <= tiers[i - 1].max) {
                    errorMsg = `Tier ${tiers[i].index} overlaps with Tier ${tiers[i - 1].index}. Adjust min/max ranges.`; break;
                }
            }
        }
    }

    if (errorMsg) {
        e.preventDefault();
        Toastify({
            text: errorMsg,
            duration: 4000,
            close: true,
            gravity: "top",
            position: "center",
            backgroundColor: "#e74c3c",
            style: { fontWeight: "500", borderRadius: "10px" }
        }).showToast();
        return false;
    }
});
