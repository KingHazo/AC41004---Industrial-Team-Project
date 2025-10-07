// investor_portal_home.js

document.addEventListener('DOMContentLoaded', () => {

    const filterBtn = document.getElementById('filterButton');
    const tagsContainerWrapper = document.getElementById('tagFiltersContainer');

    if (filterBtn && tagsContainerWrapper) {
        if (tagsContainerWrapper.querySelector('.filter-tag.selected:not([data-tag="0"])')) {
             tagsContainerWrapper.style.display = 'block'; // Show if filtered
        }

        filterBtn.addEventListener('click', () => {
            if (tagsContainerWrapper.style.display === 'none' || tagsContainerWrapper.style.display === '') {
                tagsContainerWrapper.style.display = 'block'; 
                filterBtn.classList.add('active'); 
            } else {
                tagsContainerWrapper.style.display = 'none';
                filterBtn.classList.remove('active');
            }
        });
    }

    const tagContainer = document.getElementById('pitch-tags');
    
    const applyTagFilter = () => {
        const selectedTags = [];
        document.querySelectorAll('#pitch-tags .filter-tag.selected').forEach(button => {
            const tagId = button.getAttribute('data-tag');
            if (tagId !== '0') {
                selectedTags.push(tagId);
            }
        });

        const url = new URL(window.location.href);

        if (selectedTags.length > 0) {
            url.searchParams.set('tag_id', selectedTags.join(','));
        } else {
            // default to all tags
            url.searchParams.delete('tag_id');
        }

        window.location.href = url.toString();
    };

    if (tagContainer) {
        tagContainer.addEventListener('click', (event) => {
            const button = event.target.closest('.filter-tag');
            
            if (button) {
                const tagId = button.getAttribute('data-tag'); 
                const allButton = document.querySelector('#pitch-tags .filter-tag[data-tag="0"]');

                if (tagId === '0') {
                    if (!button.classList.contains('selected')) {
                        document.querySelectorAll('#pitch-tags .filter-tag').forEach(btn => {
                            btn.classList.remove('selected');
                        });
                        button.classList.add('selected');
                    } else {
                        return; 
                    }
                } else {
                    button.classList.toggle('selected');

                    const specificTagsSelected = document.querySelectorAll('#pitch-tags .filter-tag.selected:not([data-tag="0"])').length;

                    if (specificTagsSelected > 0) {
                        // deselect all when other tag is picked
                        allButton.classList.remove('selected');
                    } else {
                        // all is selected if no other tags
                        allButton.classList.add('selected');
                    }
                }
                
                // apply filter and refresh
                applyTagFilter();
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
