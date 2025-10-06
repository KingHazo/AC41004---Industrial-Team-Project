// investor_portal_home.js

document.addEventListener('DOMContentLoaded', () => {

    const tagContainer = document.getElementById('pitch-tags');
    
    // tag filtering logic
    if (tagContainer) {
        tagContainer.addEventListener('click', (event) => {
            const button = event.target.closest('.filter-tag');
            
            if (button) {
                console.log("Filter button clicked:", button.textContent.trim());

                const tagId = button.getAttribute('data-tag'); 
                
                const url = new URL(window.location.href);
                
                if (tagId === '0') {
                    url.searchParams.delete('tag_id');
                    console.log("Setting filter to: All (URL param deleted)");
                } else {
                    url.searchParams.set('tag_id', tagId);
                    console.log("Setting filter to TagID:", tagId);
                }
                
                console.log("Navigating to:", url.toString());
                window.location.href = url.toString();
            }
        });
    } else {
        console.error("Tag container #pitch-tags not found.");
    }

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
