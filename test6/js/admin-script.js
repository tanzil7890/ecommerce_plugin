
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('pcm-search');
    const collectionsGrid = document.getElementById('pcm-collections-grid');
    const collectionItems = collectionsGrid.getElementsByClassName('pcm-collection-item');
    const noResultsMessage = document.getElementById('no-results-message');
    const noCollectionsMessage = document.getElementById('no-collections-message');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let visibleCount = 0;

        Array.from(collectionItems).forEach(function(item) {
            const collectionName = item.getAttribute('data-name');
            if (collectionName.includes(searchTerm)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0 && collectionItems.length > 0) {
            noResultsMessage.style.display = 'block';
            noCollectionsMessage.style.display = 'none';
        } else {
            noResultsMessage.style.display = 'none';
            noCollectionsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    });
});
