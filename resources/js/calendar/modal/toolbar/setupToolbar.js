var searchBox = document.getElementById('user-search-box');
var modalSearchBox = document.getElementById('modal-user-search-box');
var modalLabelBox = document.getElementById('modal-label-input-container');

export function toggleToolbarHighlight(selectedIcon) {

    document.querySelectorAll('.toolbar').forEach(icon => {
        icon.classList.remove('button-highlight');
    });

    selectedIcon.classList.toggle('button-highlight');
}

export function toggleSearch(event) {
    
    searchBox.classList.remove('hidden');
    modalSearchBox.classList.remove('hidden');
    
    modalLabelBox.classList.add('hidden');

    toggleToolbarHighlight(event.currentTarget);
}

export function toggleLabel(event) {
    
    searchBox.classList.add('hidden');
    modalSearchBox.classList.add('hidden');

    modalLabelBox.classList.remove('hidden');

    toggleToolbarHighlight(event.currentTarget);
}