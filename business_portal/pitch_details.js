console.log("pitch_details.js loaded");

let year = document.querySelector('.day');
if (year) {
  year.textContent = new Date().getFullYear();
}

// example button actions
const editBtn = document.querySelector('.edit-btn');
const profitBtn = document.querySelector('.profit-btn');

// select the elements to make editable
const elevatorPitch = document.getElementById('elevatorPitchText');
const detailedPitch = document.getElementById('detailedPitchText');
const saveBtn = document.getElementById('saveBtn');


// edit button enables inline editing
if (editBtn) {
  editBtn.addEventListener('click', () => {
    if (elevatorPitch && detailedPitch) {
      elevatorPitch.innerHTML = `<textarea id="elevatorPitchInput" rows="4" cols="50">${elevatorPitch.textContent.trim()}</textarea>`;
      detailedPitch.innerHTML = `<textarea id="detailedPitchInput" rows="6" cols="50">${detailedPitch.textContent.trim()}</textarea>`;

      editBtn.style.display = "none";
      saveBtn.style.display = "inline-block"; // now shows correctly
    }
  });
}


// save button sends changes to backend
if (saveBtn) {
  saveBtn.addEventListener('click', () => {
    const elevatorPitchValue = document.getElementById('elevatorPitchInput').value;
    const detailedPitchValue = document.getElementById('detailedPitchInput').value;

    fetch(window.location.href, { // POST to current page
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        elevatorPitch: elevatorPitchValue,
        detailedPitch: detailedPitchValue
      })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Update text and remove textarea
          elevatorPitch.textContent = elevatorPitchValue;
          detailedPitch.textContent = detailedPitchValue;

          saveBtn.style.display = "none";
          editBtn.style.display = "inline-block";
          window.scrollTo({ top: 0, behavior: 'smooth' });


          // show message
          const msg = document.getElementById('saveMessage');
          msg.style.display = 'block';

          // hide after 3 seconds
          setTimeout(() => {
            msg.style.display = 'none';
          }, 3000);

        } else {
          alert("Failed to save pitch.");
        }
      })
      .catch(err => {
        console.error("Error saving pitch:", err);
        alert("An error occurred while saving.");
      });
  });
}

if (profitBtn) {
  profitBtn.addEventListener('click', () => {
    window.location.href = "profit_declare.php";
  });
}