// investor-portal-home.js

document.addEventListener('DOMContentLoaded', () => {

    // all buttons
    const moreBtns = document.querySelectorAll('.more-btn');

    moreBtns.forEach(button => {
        button.addEventListener('click', (e) => {
            // read the pitch ID stored in the data attribute
            const pitchId = e.currentTarget.getAttribute('data-pitch-id');

            if (pitchId) {
                // make the correct URL and navigate
                window.location.href = `investor_pitch_details.php?id=${pitchId}`;
            } else {
                console.error("Error: Pitch ID not found on button.");
            }
        });
    });

    // invest button
    const investBtns = document.querySelectorAll('.invest-btn');
    investBtns.forEach(button => {
        button.addEventListener('click', (e) => {
             const pitchId = e.currentTarget.closest('.card').querySelector('.more-btn').getAttribute('data-pitch-id');
             if (pitchId) {
                window.location.href = `investor_pitch_details.php?id=${pitchId}`;
             }
        });
    });
    
});
