// investor_portal_home.js

document.addEventListener('DOMContentLoaded', () => {

    // filter stuff
    const filterBtn = document.getElementById('filterButton');
    const tagsContainerWrapper = document.getElementById('tagFiltersContainer');
    const tagContainer = document.getElementById('pitch-tags');
    const searchInput = document.getElementById('searchInput');

    // get search and tags
    const applyFiltersAndNavigate = () => {
        const selectedTags = [];

        document.querySelectorAll('#pitch-tags .filter-tag.selected').forEach(button => {
            const tagId = button.getAttribute('data-tag');
            if (tagId !== '0') {
                selectedTags.push(tagId);
            }
        });

        const searchTerm = searchInput ? searchInput.value.trim() : '';

        const url = new URL(window.location.href);

        // tags
        if (selectedTags.length > 0) {
            url.searchParams.set('tag_id', selectedTags.join(','));
        } else {
            // default all tags if none selects
            url.searchParams.delete('tag_id');
        }

        // search
        if (searchTerm) {
            url.searchParams.set('search_term', searchTerm);
        } else {
            url.searchParams.delete('search_term');
        }

        window.location.href = url.toString();
    };

    // search listener
    if (searchInput) {
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault(); 
                applyFiltersAndNavigate();
            }
        });
    }

    // filter logic
    if (filterBtn && tagsContainerWrapper) {
        const hasActiveFilters = tagsContainerWrapper.querySelector('.filter-tag.selected:not([data-tag="0"])') || (searchInput && searchInput.value.trim() !== '');
        
        if (hasActiveFilters) {
             tagsContainerWrapper.style.display = 'block';
        }

        filterBtn.addEventListener('click', () => {
            // change the visibility of the tag container
            if (tagsContainerWrapper.style.display === 'none' || tagsContainerWrapper.style.display === '') {
                tagsContainerWrapper.style.display = 'block'; 
                filterBtn.classList.add('active'); 
            } else {
                tagsContainerWrapper.style.display = 'none';
                filterBtn.classList.remove('active');
            }
        });
    }

    if (tagContainer) {
        tagContainer.addEventListener('click', (event) => {
            const button = event.target.closest('.filter-tag');
            
            if (button) {
                const tagId = button.getAttribute('data-tag'); 
                const allButton = document.querySelector('#pitch-tags .filter-tag[data-tag="0"]');

                if (tagId === '0') {
                    // all button clicked
                    if (!button.classList.contains('selected')) {
                        // clear other selections
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
                        allButton.classList.remove('selected');
                    } else {
                        allButton.classList.add('selected');
                    }
                }
                
                applyFiltersAndNavigate();
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
